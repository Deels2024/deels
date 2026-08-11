<div class="msg {{$message->user->id == Auth::id() ? 'right-msg' : 'left-msg'}}">
    <div
            class="msg-img"
            style="background-image: url('{{ $message->user->avatar() }}')"
    ></div>

    <div class="msg-bubble">
        <div class="msg-info">
            <div class="msg-info-name">{{$message->user->id == 0 ? 'DEELS' : $message->user->username}}</div>
            <div class="msg-info-time">{{\Carbon\Carbon::parse($message->created_at)->format('H:i')}}</div>
        </div>

        <div class="msg-text">
            {!! $message->body !!}
            @if($message->button)
                <a href="{{$message->button['url']}}" class="btn btn-sm btn-small mt-2">{{$message->button['text']}}</a>
            @endif
        </div>
    </div>
</div>


{{--<div class="chat-message chat-message {{$message->user->id == Auth::id() ? 'chat-message--right' : ''}}">--}}
{{--    <div class="chat-item">--}}
{{--        <div class="chat-item__avatar chat-item__avatar--xs" style="background-image: url('{{ $message->user->avatar() }}');"></div>--}}
{{--        @if($message->user_id == 0)--}}
{{--            <p>--}}
{{--
{{--                @if($message->button)--}}
{{--                    <br>--}}
{{--                    <a href="{{$message->button['url']}}" class="btn btn-sm btn-small mt-2">{{$message->button['text']}}</a>--}}
{{--                @endif--}}
{{--            </p>--}}
{{--        @else--}}
{{--            <p>--}}
{{--                {{ $message->body }}--}}
{{--                @if($message->button)--}}
{{--                    <br>--}}
{{--                    <a href="{{$message->button['url']}}" class="btn btn-sm btn-small mt-2">{{$message->button['text']}}</a>--}}
{{--                @endif--}}
{{--            </p>--}}
{{--        @endif--}}
{{--        @if($message->user->id != Auth::id() && $message->user->id != 0)--}}
{{--            <span class="abuse" data-user="{{$message->user->id}}">!</span>--}}
{{--        @endif--}}
{{--    </div>--}}
{{--</div>--}}