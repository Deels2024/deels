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
                <h1 class="account-main__title">Модерация комментариев</h1>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <div class="comments bg-dark">
            @if($comments->count())
                @foreach($comments as $comment)
                    <div class="comment-item d-flex">
                        <p class="comment-id comment-id--head mr-1">#{{$comment->id}}</p>
                        <div class="comment-item__content">
                            <div class="comment-head">
                                <div class="avatar avatar--sm mr-1">
                                    @if($comment->user_id)
                                        <img src="{{$comment->author->avatar()}}" class="magnific_image circle-img" data-image="{{$comment->author->avatar()}}" alt="{{$comment->author_name}}">
                                    @else
                                        <img src="{{avatar_by_email($comment->author_email)}}" class="magnific_image circle-img" data-image="{{avatar_by_email($comment->author_email)}}" alt="{{$comment->author_name}}">
                                    @endif
                                </div>
                                <div class="comment-head__content">
                                    <p class="comment-email">{{$comment->author_name}} ({{$comment->author_email}}
                                        )@if($comment->user_id===Auth::id())
                                            (Вы)
                                        @endif</p>
                                    <p class="comment-date">{{$comment->created_at->diffForHumans()}}</p>
                                </div>
                            </div>
                            <div class="comment-body">
                                <p class="comment-text">
                                    {!! safe_output(nl2br($comment->comment)) !!}

                                    @if($comment->campaign_id)
                                        @if($comment->campaign)
                                            <br><br>
                                            <a href="{{route('campaign_single', $comment->campaign->slug)}}" target="_blank">
                                                [ Копилка {{$comment->campaign->title}} ]
                                            </a>
                                        @else
                                            <br><br>
                                            <span style="color: #605d5d">[ Копилка удалена ]</span>
                                        @endif
                                    @endif
                                    @if($comment->story_id)
                                        @if($comment->story)
                                            <br><br>
                                            <a href="{{route('admin_stories') }}?story_id={{$comment->story->id}}" target="_blank">
                                               [ Сторис ID {{$comment->story->id}} ]
                                            </a>
                                        @else
                                            <br><br>
                                            <span style="color: #605d5d">[ Сторис удалена ]</span>
                                        @endif
                                    @endif
                                </p>

                                <div class="d-flex ai-center">
                                    <div class="actions">
                                        <a class="actions-link" href="{{$comment->campaign ? route('campaign_single', $comment->campaign->slug) : '#'}}" style="background-image: url(/dist/images/admin_icons/icon-eye.svg)"></a>
                                        <a class="actions-link comment_action" data-action="trash" data-id="{{$comment->id}}" href="javascript:;" style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></a>
                                        @if ($comment->approved != 1)
                                            <a class="actions-link comment_action" data-action="approve" data-id="{{$comment->id}}" href="javascript:;" style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></a>
                                        @endif
                                    </div>
                                    <p class="comment-id comment-id--body mr-1">#{{$comment->id}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        {{$comments->links()}}
    </main>

@endsection

@section('page-js')
    <script>
		$(document).ready(function () {
			$('body').on('click', '.comment_action', function (e) {
				e.preventDefault();

				var $that = $(this);
				if ($that.attr('data-action') === 'trash') {
					if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
						return false;
					}
				}

				var action = $that.data('action');

				$.ajax({
					type: 'POST',
					url: '{{ route('comment_action') }}',
					data: {comment_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
					success: function (data) {
						if (data.success == 1) {

							if (action == 'approve') {
								$that.remove();
							} else {
								$that.parents('.comment-item').remove();
							}

						}
					}
				});
			});
		});
    </script>

@endsection

