@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">Модерация челленджей
                    {{isset($_GET['story_id']) ? ' ID'.$_GET['story_id'] :''}}
                  @if(isset($_GET['type']))
                    @if($_GET['type'] == 'declined')
                        [Отклоненные]
                    @endif
                      @if($_GET['type'] == 'active')
                        [Одобренные]
                    @endif
                  @endif
                </h1>
            </div>
        </div>

        <div class="d-flex mb-4">
            <a href="{{route('admin_challenges')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">На модерации</a>
            <a href="{{route('admin_challenges')}}?type=declined" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Отклоненные</a>
            <a href="{{route('admin_challenges')}}?type=active" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Одобренные</a>
            <a href="{{route('admin_challenges')}}?type=blocked" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Заблокированные</a>
        </div>
        <form action="{{route('admin_challenges')}}" type="GET" class="d-flex mb-4">
            <div class="">
                <input id="story_id" class="new__input" type="text" placeholder="№ сторис" name="story_id" value="{{$_GET['story_id'] ?? ''}}">
            </div>
            @if(isset($_GET['type']))
                <input type="hidden" name="type" value="{{$_GET['type']}}">
            @endif
            @if(isset($_GET['type']) && $_GET['type'] == 'declined')
                <div class="" style="margin-left: 10px">
                    <label class="d-flex ai-center mt-1 mb-2">
                        <input class="mr-1" type="checkbox" name="ai_moderated" value="1" {{isset($_GET['ai_moderated']) ? 'checked' : ''}}><span>Отклонено ИИ</span>
                    </label>
                </div>
            @endif
            <div class="ml-4">
                <button type="submit" class="btn btn-small ml-4" style="padding: 5px; max-height: auto">Применить</button>
            </div>
        </form>
        <div class="comments bg-dark">

            @if($challenges && count($challenges))
                <section class="challenge pb-8 pt-8">
                    <div class="challenge-grid" style="--challenge-grid: repeat(3, 1fr)">
                        @foreach($challenges as $challenge)
                            @include('challenges.challenge_item', ['route' => route('dashboard_challenge_page', $challenge->id), 'dashboard' => true, 'moderation' => true, 'show_ai_moderation' => true])
                        @endforeach
                    </div>
                    <div class="d-flex flex-column gap-8 ai-center jc-center pt-8">
                        @if($challenges instanceof \Illuminate\Pagination\LengthAwarePaginator )
                            {{$challenges->withQueryString()->links()}}
                        @endif
                    </div>
                </section>
            @else
                Челленджи отсутствуют
            @endif
        </div>
    </main>

    @include('stories.modal')
    @include('challenges.modal')

@endsection

@section('page-js')
{{--    @include('dashboard.stories.stories_scripts')--}}
    <script>
        $(document).ready(function () {
            $('body').on('click', '.show_challenge', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.open($(this).attr('data-route'), '_blank');
            });
            $('body').on('click', '.comment_action', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $that = $(this);
                if ($that.attr('data-action') === 'trash') {
                    if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
                        return false;
                    }
                }
                if ($that.attr('data-action') === 'restart') {
                    if (!confirm("Вы хотите перезапустить челлендж?")) {
                        return false;
                    }
                }


                var action = $that.data('action');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin_challenges.confirm') }}',
                    data: {challenge_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        console.log(data);
                        if (data.success == 1) {

                            if (action == 'approve') {
                                alert('Челлендж подтвержден');
                                $that.remove();
                            } else if (action == 'delete') {
                                alert('Челлендж удален');
                                $that.remove();
                                $that.parents('.challenge-card').remove();
                            } else if (action == 'restart') {
                                alert('Челлендж перезапущен');
                                location.reload(true);
                            }else {
                                alert('Челлендж отклонен');
                                $that.parents('.comment-item').remove();
                                $that.parents('.challenge-card').remove();
                            }

                        }
                    }
                });
            });
            $('.challenge_delete').on('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var $that = $(this);
                if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
                    return false;
                }

                var challenge_id = $(this).attr('data-id');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('challenges.remove') }}',
                    data: {challenge_id: challenge_id},
                    success: function (data) {
                        console.log(data);
                        if (data.success) {
                            $that.parents('.challenge-card').remove();
                            $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Челлендж удален!</div>')
                        } else {
                            alert('Невозможно удалить челлендж')
                        }
                    }
                });
            });
        });
    </script>

@endsection

