@if(isset($story->id))

    @php
        $is_viewed = $story->getAttribute('is_viewed');
        if($is_viewed === null && Auth::user()) {
            $is_viewed = \App\Models\View::where('user_id', Auth::user()->id)
                ->where('story_id', $story->id)
                ->exists();
        }
        $is_viewed = (bool) $is_viewed;
    @endphp
    @if(isset($challenge))
        <a href="#story-popup" class="challenge-card show_story {{$story->paid && !$is_viewed ? 'story_paid story__content_closed' : ''}}" data-route="{{route('stories.preview', ['id' => $story->id, 'user_id' => Auth::user()->id ?? null])}}" data-story="{{$story->id}}" data-type="{{$story->type}}" data-paid="{{$story->paid}}" data-amount="{{$story->amount}}">
            <div class="challenge-card__head">
                <img src="{{$story->user ? $story->user->avatar() : ''}}" alt="" height="40" width="40">
                <span>{{$story->user ? $story->user->fullname : ''}}</span>
            </div>
            @include('stories.parts.preview', [
                'story' => $story,
                'class' => $story->paid && $story->type != 'video' && !$is_viewed ? 'blurred_preview' : '',
            ])

            <div class="challenge-card__content">
                <div style="text-align: center">
                    {!! $story->frozen && !$story->banned ? '<br>[ На проверке ]' : '' !!}
                    {!! $story->banned ? '<br><span style="color:#ff0000">[ Заблокировано ]</span>' : '' !!}
                    @if($story->banned)
                        <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                            {!! $story->banned_reason ?? 'Бан за нарушение правил'!!}
                        </div>
                    @endif
                </div>
                <div class="play-btn copystories-btn"  {!! $story->paid && !$is_viewed ? 'style="display:none"' : '' !!}></div>
                @include('stories.parts.stats', ['story' => $story, 'class' => 'mt-7'])
            </div>
        </a>
    @else

    <a href="#story-popup" class="{{!isset($list) ? 'tops-story' : ''}} copystories-item show_story {{$story->paid && !$is_viewed ? 'story_paid story__content_closed' : ''}}" data-route="{{route('stories.preview', ['id' => $story->id, 'user_id' => Auth::user()->id ?? null])}}" data-story="{{$story->id}}" data-type="{{$story->type}}" data-paid="{{$story->paid}}" data-amount="{{$story->amount}}">
        <div class="tops-story__head">
            <img src="{{$story->user ? $story->user->avatar() : ''}}" class="tops-story__avatar" alt="" height="30" width="30">
            <div class="tops-story__name">{{$story->user ? $story->user->fullname : ''}}</div>
        </div>
        @include('stories.parts.preview', [
            'story' => $story,
            'class' => 'copystories-item__img ' . ($story->paid && $story->type != 'video' && !$is_viewed ? 'blurred_preview' : ''),
        ])
        <div class="copystories-item__content">
            <div class="play-btn copystories-btn" {!! $story->paid && !$is_viewed ? 'style="display:none"' : '' !!}></div>
            @include('stories.parts.stats', ['story' => $story])
        </div>
    </a>
    @endif
@endif
