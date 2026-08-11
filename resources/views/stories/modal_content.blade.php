@if(is_array($data))

@php

@endphp
@if($data['paid'] && !$data['show_story'] && !$data['is_viewed'])
<div class="story-protect">
    <div class="story-protect__content">
        <div class="story-protect__title">
            Донат за просмотр <br>Сторис:
            <span>
                {{$data['amount']}}
                <img src="/dist/img/deels_cur.svg" class="small_coin">
            </span>
        </div>
        <a href="#" class="btn btn_fill donate_story" data-route="{{route('stories.donate', ['id' => $data['story_id'], 'user_id' => $auth_user->id ?? Auth::user()->id ?? null])}}" data-story="{{$data['story_id']}}" data-type="{{$data['type']}}" data-paid="{{$data['paid']}}" data-amount="{{$data['amount']}}">Задонатить</a>
    </div>
</div>
@endif

<div class="story-media popup-story-content" style="position: relative;" data-story-id="{{$data['story_id']}}">
    <style>
        .story-nav-arrow {
            position: absolute;
            top: 36%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.30);
            color: #fff;
            z-index: 20;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity .2s ease;
        }
        .story-nav-arrow:hover {
            background-color: rgba(0, 0, 0, 0.50);
        }

        .story-nav-arrow.story-nav-prev {
            left: 12px;
        }

        .story-nav-arrow.story-nav-next {
            right: 12px;
        }

        .story-nav-arrow.disabled {
            opacity: .35;
            cursor: default;
            pointer-events: none;
        }

        .story-nav-arrow svg {
            width: 18px;
            height: 18px;
        }
    </style>

    <button type="button" class="story-nav-arrow story-nav-prev" aria-label="Предыдущая сторис">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M14.5 6L8.5 12L14.5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <button type="button" class="story-nav-arrow story-nav-next" aria-label="Следующая сторис">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M9.5 6L15.5 12L9.5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    @if($data['paid'] && !$data['show_story'] && !$data['is_viewed'])
        @if($data['type'] == 'video')
            @if($data['video_preview'])
                <video id="videoPlayer" src="{{$data['video_preview']}}" loop autoplay playsinline></video>
            @else
                <img src="{{$data['thumbnail']}}" alt="" class="blurred blurred_preview">
            @endif
        @else
            <img src="{{$data['path']}}" alt="" class="blurred blurred_preview">
        @endif
    @else
       @if($data['type'] == 'video')
           @if(isset($data['hls_urls']) && $data['hls_urls'])
                <video id="videoPlayer"
                       class="video-js vjs-default-skin vjs-big-play-centered"
                       data-video="{{$data['hls_url']}}"
                       autoplay
                       loop
                       controls
                       playsinline
                       preload="auto"
                       data-setup='{}'>
                </video>
            @else
                <video id="videoPlayer" src="{{route('stories.get.video', [$data['story_id'], 'v' => time()])}}" loop controls playsinline></video>
            @endif
        @else
            <img src="{{$data['path']}}" alt="">
       @endif
   @endif
    <script>
        (function () {
            var $media = $('.popup-story-content');
            if (!$media.length) {
                return;
            }

            $media.off('click.storyVideoToggle touchend.storyVideoToggle pointerup.storyVideoToggle');

            function showVideoStateIcon(type) {
                $media.find('.play-btn.copystories-btn, .pause-btn.copystories-btn').remove();

                var $icon = $('<div></div>', {
                    class: type + '-btn copystories-btn'
                }).css({
                    position: 'absolute',
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%, -50%)',
                    zIndex: 15,
                    pointerEvents: 'none',
                    display: 'block'
                });

                if (type === 'pause') {
                    $icon.css('background-image', 'none');
                    $icon.css('position', 'absolute');
                    $icon.css('display', 'block');
                    $icon.css('overflow', 'hidden');
                    $icon.css('border-radius', '50px');
                    $icon.css('background', 'linear-gradient(90deg, #B224EF 0%, #7579FF 100%)');
                    $icon.append('<span style="position:absolute;left:16px;top:12px;width:4px;height:22px;background:#fff;border-radius:3px;"></span>');
                    $icon.append('<span style="position:absolute;left:26px;top:12px;width:4px;height:22px;background:#fff;border-radius:3px;"></span>');
                }

                $media.append($icon);

                setTimeout(function () {
                    $icon.fadeOut(200, function () {
                        $(this).remove();
                    });
                }, 500);
            }

            var lastVideoToggleAt = 0;
            function handleVideoToggle(e) {
                e.preventDefault();
                e.stopPropagation();

                var now = Date.now();
                if (now - lastVideoToggleAt < 250) {
                    return;
                }
                lastVideoToggleAt = now;

                var video = this;

                if (video.paused) {
                    var playPromise = video.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {});
                    }
                    showVideoStateIcon('play');
                } else {
                    video.pause();
                    showVideoStateIcon('pause');
                }
            }

            $media.on('click.storyVideoToggle touchend.storyVideoToggle pointerup.storyVideoToggle', 'video', handleVideoToggle);

            var currentPath = (window.location.pathname || '').replace(/\/+$/, '');
            var isStoriesCatalog = currentPath === '/stories';
            if (!isStoriesCatalog) {
                $media.find('.story-nav-arrow').remove();
                return;
            }

            if (window.storyModalNavigator && typeof window.storyModalNavigator.syncFromModal === 'function') {
                window.storyModalNavigator.syncFromModal();
            }
        })();
    </script>
</div>
<div class="story-aside">
    <div class="story-aside__nav">
        <button class="{{$data['is_liked'] ? 'active' :''}} story-aside__btn {{$auth_user ? 'add_like' : 'need_auth'}}" data-route="{{route('stories.like.web')}}" data-story="{{$data['story_id']}}" {!! isset($data['likes_count']) && $data['likes_count'] > 0 ? 'data-count="'.$data['likes_count'].'"' : '' !!}>
            <svg width="26" height="21" viewBox="0 0 26 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.9375 1C4.52031 1 1.75 3.6902 1.75 7.00857C1.75 13.0171 9.0625 18.4795 13 19.75C16.9375 18.4795 24.25 13.0171 24.25 7.00857C24.25 3.6902 21.4797 1 18.0625 1C15.97 1 14.1194 2.00889 13 3.5531C12.4294 2.7639 11.6715 2.11983 10.7902 1.67541C9.90901 1.23099 8.93048 0.99932 7.9375 1Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

        </button>

        <button class="story-aside__btn {{$auth_user ? '' : 'need_auth'}}" {!! $auth_user ? 'data-story-nav="#storyComments"' : '' !!} {!! isset($data['comments_count']) && $data['comments_count'] > 0 ? 'data-count="'.$data['comments_count'].'"' : '' !!}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22.5C14.225 22.5 16.4001 21.8842 18.2502 20.7304C20.1002 19.5767 21.5422 17.9368 22.3936 16.0182C23.2451 14.0996 23.4679 11.9884 23.0338 9.95155C22.5997 7.91475 21.5283 6.04383 19.9549 4.57538C18.3816 3.10693 16.377 2.1069 14.1948 1.70176C12.0125 1.29661 9.75048 1.50455 7.69481 2.29927C5.63914 3.09399 3.88213 4.4398 2.64597 6.16652C1.4098 7.89323 0.75 9.9233 0.75 12C0.75 13.736 1.2 15.3717 2 16.8148L0.75 22.5L6.84125 21.3333C8.38625 22.0788 10.1412 22.5 12 22.5Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        @if(!$data['challenge_id'])
        <a href="#" class="story-aside__btn {{$auth_user ? '' : 'need_auth'}}" {!! $auth_user ? 'data-story-nav="#storydonate"' : '' !!}>
            <svg width="29" height="29" viewBox="0 0 29 29" fill="none" stroke="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.7552 17.7021C26.8254 16.0237 27.3924 14.0739 27.3889 12.0833C27.3889 6.29944 22.7006 1.61111 16.9167 1.61111C14.8488 1.61111 12.9211 2.21044 11.2979 3.24397C10.2177 3.30456 9.14845 3.49331 8.11275 3.80625C8.90001 2.96932 9.8021 2.24841 10.792 1.66508C12.6359 0.578598 14.7362 0.00379786 16.8764 0H16.9167C23.5899 0 29 5.41011 29 12.0833C29 12.1921 28.9984 12.3 28.996 12.408L28.9944 12.4466C28.9368 14.4764 28.3658 16.4585 27.3349 18.208C26.7516 19.1979 26.0307 20.1 25.1938 20.8873C25.5007 19.8698 25.6932 18.8033 25.7552 17.7021Z" fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M24.1667 16.9167C24.1667 23.5899 18.7566 29 12.0833 29C5.41011 29 0 23.5899 0 16.9167C0 10.2434 5.41011 4.83333 12.0833 4.83333C18.7566 4.83333 24.1667 10.2434 24.1667 16.9167ZM22.5556 16.9167C22.5556 22.7006 17.8672 27.3889 12.0833 27.3889C6.29944 27.3889 1.61111 22.7006 1.61111 16.9167C1.61111 11.1328 6.29944 6.44444 12.0833 6.44444C17.8672 6.44444 22.5556 11.1328 22.5556 16.9167Z" fill="white"/>
                <path d="M12.9372 18.4472H10.6333V19.5267H14.4194V20.7833H10.6333V22.5556H8.78056V20.7833H7.49167V19.5267H8.78056V18.4472H7.49167V16.7878H8.78056V11.2778H12.9372C13.9576 11.2778 14.8222 11.6322 15.5311 12.3411C16.24 13.0285 16.5944 13.8824 16.5944 14.9028C16.5944 15.9017 16.24 16.7448 15.5311 17.4322C14.8222 18.1089 13.9576 18.4472 12.9372 18.4472ZM12.9372 13.0178H10.6333V16.7878H12.9372C13.4528 16.7878 13.877 16.6052 14.21 16.24C14.5644 15.8748 14.7417 15.4291 14.7417 14.9028C14.7417 14.3765 14.5644 13.9307 14.21 13.5656C13.877 13.2004 13.4528 13.0178 12.9372 13.0178Z" fill="white"/>
            </svg>
        </a>
        @endif
        <button class="story-aside__btn" data-story-nav="#storyShare">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.3496 22.1123L20.2412 25.4273M36.2602 14.208C36.4032 14.1904 36.5483 14.2133 36.6789 14.2743C36.8094 14.3352 36.9202 14.4317 36.9986 14.5526C37.0769 14.6735 37.1196 14.814 37.1219 14.9581C37.1242 15.1021 37.0858 15.2439 37.0113 15.3673L23.6344 37.3646C23.5544 37.4961 23.4368 37.6005 23.2967 37.6643C23.1566 37.728 23.0006 37.7483 22.8489 37.7223C22.6972 37.6963 22.5568 37.6254 22.4459 37.5186C22.335 37.4119 22.2587 37.2743 22.227 37.1236L19.8724 25.9945C19.833 25.809 19.7264 25.6446 19.573 25.5331L10.3684 18.8479C10.2443 18.7575 10.1501 18.6319 10.0981 18.4874C10.0461 18.3429 10.0386 18.1861 10.0766 18.0373C10.1146 17.8885 10.1963 17.7545 10.3113 17.6526C10.4262 17.5508 10.5691 17.4857 10.7214 17.4658L36.2602 14.208Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

    </div>
</div>


<div class="story-bottom">
    <div class="story-autor  {{$data['description_with_tags'] && strlen($data['description_with_tags']) <= 86 ? 'not_faded' : ''}}">
        @if(isset($data['user']))
            @php

                try {
                    $user_avatar = isset($data['user']['avatar_url']) ? $data['user']['avatar_url'] : '/default_avatars/avatar_1.png';
                    $user_name = $data['user']['fullname'] ?? 'Пользователь удален';
                } catch (Throwable $e) {
                     $user_avatar =  '/default_avatars/avatar_1.png';
                     $user_name = 'Пользователь удален';
                }
            @endphp
            <a href="{{isset($data['user']['id']) ? route('user.profile', $data['user']['id']) : '#'}}" class="story-autor__avatar-link">
                <img src="{{$user_avatar}}" class="story-autor__avatar">
            </a>
        @endif
        <div class="story-autor__content">
            <a href="{{isset($data['user']['id']) ? route('user.profile', $data['user']['id']) : '#'}}">
            @if(isset($data['user']))
                <h3 class="story-autor__name" style="margin-top: 6px">{{$user_name}}</h3>
            @else
                <h3 class="story-autor__name" style="margin-top: 6px">Пользователь удален</h3>
            @endif
            </a>
            <p class="story-autor__subinfo">
                {!! nl2br($data['description_with_tags']) !!}
                <span class="story_number">st#{{$data['story_id']}} {{$data['created_at'] ?? ''}}</span>
            </p>

        </div>

    </div>
    @if(isset($data['challenge_id']) && isset($data['challenge']['url']))
        <a class="story-badge" href="{{$data['challenge']['url']}}">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path class="st0" d="M9.8,8.3l-3.7,5.5l0.8-3.7L7.1,9l0,0L5.7,8.8l1.9-5.6L9,3.4L7.6,6.9L7.3,7.8L8.3,8L9.8,8.3z"  stroke="white" stroke-width="1.5"/>
            </svg>
            <span><b>Челлендж</b> {{$data['challenge']['title']}}
                @if(isset($data['challenge']['declined']) && $data['challenge']['declined'])
                    <span style="color: red; font-size: 9px; text-transform: uppercase; font-weight: bold">(Удалён)</span>
                @endif
            </span>
        </a>
    @endif
</div>


<div class="story-offcanvas" id="storyComments">
    <div class="story-offcanvas__head">
        <button class="story-offcanvas__close"></button>
    </div>
    <div class="story-offcanvas__body">
        <div class="story-comment">
            <div class="story-comment__list" data-story="{{$data['story_id']}}">
                @if(count($data['comments']) > 0)
                @foreach($data['comments'] as $comment)
                    <div class="story-comment__item">
                        <a href="{{route('user.profile', $comment['user']['id'])}}">
                            <img src="{{$comment['user']['avatar_url']}}" alt="" class="story-comment__avatar">
                        </a>
                        <div class="story-comment__content">
                            <div class="story-comment__name">
                                <a href="{{route('user.profile', $comment['user']['id'])}}">{{$comment['user']['fullname']}}</a>
                                @if($auth_user && $comment['user']['id'] != $auth_user->id)
                                    <span class="abuse" data-user="{{$comment['user']['id']}}">!</span>
                                @endif
                                <span class="comment_date">
                                    {{ instagram_time_ago($comment['created_at']) }}
                                </span>
                            </div>
                            <div class="story-comment__text">{{$comment['comment']}}</div>
                            <button class="story-aside__btn add_comment_like {{$comment['is_liked'] ? 'active' : ''}}" data-comment-id="{{$comment['id']}}">
                                @php
                                    $likes_count = $comment['likes_count'];
                                @endphp
                                <span class="count" {!! $likes_count > 0 ? '' : 'style="display:none"' !!}>{{$likes_count}}</span>
                                <svg width="26" height="21" viewBox="0 0 26 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.9375 1C4.52031 1 1.75 3.6902 1.75 7.00857C1.75 13.0171 9.0625 18.4795 13 19.75C16.9375 18.4795 24.25 13.0171 24.25 7.00857C24.25 3.6902 21.4797 1 18.0625 1C15.97 1 14.1194 2.00889 13 3.5531C12.4294 2.7639 11.6715 2.11983 10.7902 1.67541C9.90901 1.23099 8.93048 0.99932 7.9375 1Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
                @else
                   <div class="no_comments text-center story-comment__text">Комментариев нет</div>
                @endif
                @if(isset($user) && $user)
                    <div class="abuse_modal">
                        <form action="{{route('user.abuse')}}" class="abuse_form">
                            <input type="hidden" name="user_id" class="abuse_user_id" value=""/>
                            <input type="hidden" name="abuser_id" class="abuse_abuser_id" value="{{$auth_user->id}}"/>
                            <input id="name" class="new__input abuse_reason" type="text" placeholder="Укажите причину жалобы" name="abuse" value="">
                            <div class="mt-4">
                                <button class="btn btn-small" type="submit">Отправить</button>
                                <a class="btn btn-small btn-grey ml-2 abuse_close">Отмена</a>
                            </div>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
    @if($auth_user)
        <div class="story-offcanvas__footer">
            @php
             $blocked = false;
             try {
                 if($auth_user->blockedBy($data['user']['id'] ?? 0)) {
                     $blocked = true;
                 }
             }  catch (Throwable $e) {
                 $blocked = true;
             }
            @endphp
            @if($blocked)
                Вы не можете оставлять комментарии у этого пользователя
            @else
            <form action="#" class="story-form">
                <div class="story-form__item">
                    <img src="{{$user->avatar_url}}" alt="">
                    <textarea name="story_comment" id="" rows="1" placeholder="Добавить комментарий" required></textarea>
                    <button class="post_comment" data-story="{{$data['story_id']}}"></button>
                </div>
            </form>
            @endif
        </div>
    @endif
</div>
<div class="story-offcanvas" id="storyShare">
    <div class="story-offcanvas__head">
        <div class="story-offcanvas__title">Поделиться</div>
        <button class="story-offcanvas__close"></button>
    </div>

    @if($auth_user)
    <div class="story-offcanvas__body">
        <div class="story-comment">
            <div class="story-comment__list">
                <div class="share-list d-flex flex-column gap-3">
                    @foreach($auth_user->getRepostTo() as $repost_user)
                    <label class="share-item">
                        <img src="{{$repost_user->avatar()}}" alt="" height="24" width="24">
                        <div>{{$repost_user->fullname}}</div>
                        <input type="checkbox" class="repost_users_checkbox" name="repost_users[]" value="{{$repost_user->id}}">
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="story-offcanvas__footer story-offcanvas__footer--gradient">
        <div class="d-flex gap-3 align-items-center justify-content-center">
            <a href="#" class="story-btn repost_btn btn_url_copy" data-url="{{$data['story_url']}}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.197 3.35508C16.87 1.67508 19.447 1.53908 20.954 3.05008C22.46 4.56208 22.324 7.15008 20.651 8.83008L18.227 11.2631M10.047 14.0001C8.53999 12.4881 8.67699 9.90008 10.349 8.22108L12.5 6.06208" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M13.954 10C15.46 11.512 15.324 14.1 13.651 15.779L11.227 18.212L8.803 20.645C7.13 22.325 4.553 22.461 3.046 20.95C1.54 19.438 1.676 16.85 3.349 15.17L5.773 12.737" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </a>
            <a href="https://vk.com/share.php?url={{$data['story_url']}}&title=Посмотрите сторис" class="story-btn repost_btn" target="_blank">
                <svg width="31" height="24" viewBox="0 0 31 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 4.88905C0 3.70858 0.796443 3.2426 1.77668 3.18047L6.34091 3.21154C6.6166 3.21154 6.86166 3.39793 6.95356 3.67751C7.99506 7.09467 9.28162 9.2071 10.8132 11.5991C10.9051 11.7855 11.0583 11.8787 11.2115 11.8787C11.334 11.8787 11.4565 11.8166 11.5484 11.6612L11.6403 11.3195L11.6709 5.94527C11.6709 5.16864 11.3034 5.04438 10.4457 4.92012C10.1087 4.85799 9.89427 4.54734 9.89427 4.23669C9.89427 4.17456 9.89427 4.11243 9.9249 4.0503C10.3538 2.7145 11.7016 2.03107 13.6008 2.03107L15.3162 2C16.7253 2 18.0119 2.6213 18.0119 4.45414V11.5059C18.1344 11.5991 18.2569 11.6612 18.4101 11.6612C18.6551 11.6612 18.9615 11.5059 19.2065 11.1021C20.7994 8.83432 22.6067 6.13166 22.8518 4.70266C22.8518 4.64053 22.8824 4.60947 22.913 4.54734C23.25 3.86391 24.1077 3.39793 24.4753 3.27367C24.5366 3.2426 24.6285 3.21154 24.751 3.21154H29.499L29.8053 3.2426C30.2648 3.2426 30.6018 3.55325 30.7549 3.83284C31.0306 4.26775 30.9694 4.73373 31 4.92012V5.13757C30.5405 7.9645 27.3547 11.1331 26.0069 13.1834C25.8231 13.432 25.7312 13.6494 25.7312 13.8669C25.7312 14.0533 25.8231 14.2396 25.9763 14.426L30.4486 20.142C30.6937 20.4837 30.8162 20.8876 30.8162 21.2293C30.8162 22.2544 29.8666 22.8447 28.9476 22.9379L28.4269 22.9689H23.7708C23.6789 22.9689 23.6176 23 23.5257 23C23.0049 23 22.5761 22.7204 22.2698 22.4098C21.2895 21.1982 20.3399 19.9556 19.3903 18.7441C19.2065 18.4956 19.1453 18.4645 18.9615 18.3402C18.747 19.2411 18.5632 20.1731 18.3488 21.105L18.2569 21.6331C18.1038 22.1923 17.7055 22.7825 16.9704 22.9379L16.5415 22.9689H13.5395C8.24012 22.9689 3.49209 15.3269 0.153162 5.78994C0.0612648 5.54142 0 5.1997 0 4.88905ZM18.4101 12.9349C17.6136 12.9349 16.7253 12.4689 16.7253 11.5991V4.45414C16.7253 3.61538 16.3577 3.30473 15.3468 3.30473L13.6008 3.36686C12.6206 3.36686 12.0692 3.52219 11.6097 3.83284C12.3142 4.17456 12.9575 4.64053 12.9575 5.94527V11.4127C12.8656 12.5 11.9773 13.2145 11.1196 13.2145C10.5375 13.2145 10.0168 12.8728 9.71047 12.3136C8.33202 10.2012 7.16798 8.21302 6.15711 5.35503L5.88142 4.54734L1.80731 4.51627C1.25593 4.51627 1.31719 4.54734 1.31719 4.82692C1.31719 5.01331 1.34783 5.26183 1.37846 5.41716L2.02174 7.15681C5.36067 15.9172 9.55731 21.6642 13.5395 21.6642H16.6028C17.0316 21.6642 17.001 21.1361 17.0929 20.8254L17.6749 18.0917C17.7974 17.8121 17.8893 17.5636 18.1038 17.3462C18.3488 17.0976 18.6245 17.0044 18.9002 17.0044C19.4822 17.0044 20.0336 17.4704 20.4012 17.9053L23.0049 21.2604C23.2194 21.6021 23.4032 21.6642 23.5257 21.6642H28.58C29.0702 21.6642 29.499 21.5089 29.499 21.1982C29.499 21.105 29.4684 20.9808 29.4071 20.8876L24.9654 15.2648C24.5978 14.7988 24.4447 14.3328 24.4447 13.8669C24.4447 13.3698 24.6285 12.8728 24.9348 12.4379C26.2213 10.4808 28.9783 7.68491 29.6215 5.38609L29.7134 4.98225C29.6828 4.82692 29.6828 4.70266 29.6522 4.54734H24.8429C24.5366 4.6716 24.2915 4.85799 24.1077 5.10651L23.9239 5.69675C23.2194 7.68491 21.2895 10.4186 19.9111 12.3136C19.4516 12.7485 18.9308 12.9349 18.4101 12.9349Z" fill="white"/>
                </svg>
            </a>
            <a href="https://t.me/share/url?url={{$data['story_url']}}" class="story-btn repost_btn" target="_blank">
                <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M26.5169 3.34116C26.2563 3.1252 25.9422 2.98377 25.6078 2.93186C25.2734 2.87995 24.9311 2.9195 24.6174 3.04632L2.96767 11.7862C2.61783 11.93 2.31998 12.1767 2.11367 12.4938C1.90736 12.8108 1.80235 13.183 1.81257 13.5611C1.8228 13.9392 1.94778 14.3053 2.17092 14.6107C2.39406 14.9161 2.70482 15.1464 3.06192 15.271L7.73334 16.8926L10.2648 25.2615C10.2696 25.2785 10.2853 25.2893 10.2926 25.305C10.3187 25.3718 10.3568 25.4332 10.405 25.4863C10.4782 25.5681 10.5728 25.6279 10.678 25.6591C10.6901 25.6639 10.6986 25.6748 10.7107 25.6772H10.7179L10.7215 25.6784C10.815 25.6985 10.9122 25.6935 11.0031 25.6639C11.0128 25.6615 11.0224 25.6615 11.0333 25.6579C11.1206 25.6272 11.1996 25.5768 11.2641 25.5104C11.2713 25.502 11.2834 25.5008 11.2907 25.4935L14.9314 21.4746L20.2444 25.589C20.567 25.8415 20.9646 25.9781 21.3742 25.9781C22.2611 25.9781 23.026 25.357 23.2085 24.4906L27.15 5.13553C27.2169 4.80695 27.1933 4.46639 27.0818 4.15017C26.9703 3.83394 26.7751 3.55391 26.5169 3.33995M11.5855 18.4816L10.7312 22.6346L8.94892 16.7404L17.7879 12.1366L11.7498 18.1759C11.6662 18.2594 11.6091 18.3657 11.5855 18.4816ZM22.0243 24.2477C22.0016 24.3573 21.9511 24.4592 21.8777 24.5436C21.8043 24.6281 21.7104 24.6922 21.605 24.7299C21.5022 24.7694 21.3909 24.7815 21.282 24.765C21.1731 24.7485 21.0704 24.7039 20.9839 24.6356L15.2286 20.1781C15.1083 20.0852 14.9574 20.0411 14.806 20.0545C14.6546 20.068 14.5139 20.138 14.4118 20.2506L11.8779 23.043L12.731 18.9021L21.4177 10.2142C21.5194 10.1121 21.5813 9.97703 21.5922 9.83334C21.6031 9.68965 21.5623 9.54679 21.4771 9.43055C21.392 9.31432 21.2681 9.23235 21.1278 9.19944C20.9875 9.16654 20.84 9.18487 20.7121 9.25112L8.19009 15.7737L3.45705 14.1279C3.33156 14.0865 3.2221 14.007 3.14392 13.9004C3.06574 13.7939 3.02272 13.6656 3.02084 13.5334C3.01482 13.4001 3.05044 13.2682 3.12276 13.1561C3.19507 13.0439 3.30051 12.9571 3.42442 12.9075L25.0705 4.16766C25.1814 4.12053 25.3033 4.10529 25.4224 4.12364C25.5415 4.142 25.6532 4.19323 25.7448 4.27157C25.8358 4.34527 25.9046 4.44283 25.9435 4.55335C25.9823 4.66388 25.9896 4.78303 25.9647 4.89749L22.0243 24.2477Z" fill="white"/>
                </svg>
            </a>
            <a href="https://wa.me/?text={{$data['story_url']}}" class="story-btn repost_btn" target="_blank">
                <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21.9077 16.1918L18.1427 14.3094C18.0315 14.2541 17.9077 14.2291 17.7837 14.237C17.6598 14.2448 17.5401 14.2852 17.4367 14.3541L15.5919 15.5835C14.6579 15.1054 13.898 14.3457 13.4199 13.4118L14.6506 11.5682C14.7195 11.4649 14.7599 11.3452 14.7678 11.2213C14.7756 11.0974 14.7506 10.9735 14.6953 10.8624L12.8128 7.09765C12.7543 6.97986 12.664 6.88077 12.5521 6.81155C12.4403 6.74233 12.3113 6.70573 12.1798 6.70588C10.994 6.70588 9.85681 7.17689 9.01834 8.01529C8.17987 8.85369 7.70882 9.9908 7.70882 11.1765C7.71193 13.8589 8.77899 16.4305 10.6759 18.3273C12.5728 20.224 15.1447 21.291 17.8274 21.2941C19.0131 21.2941 20.1504 20.8231 20.9888 19.9847C21.8273 19.1463 22.2983 18.0092 22.2983 16.8235C22.2984 16.6923 22.2619 16.5637 22.1929 16.4521C22.1239 16.3405 22.0251 16.2504 21.9077 16.1918ZM17.8274 19.8824C15.5191 19.8796 13.3061 18.9614 11.6739 17.3294C10.0417 15.6973 9.12351 13.4846 9.12071 11.1765C9.12077 10.4382 9.38785 9.72492 9.87267 9.16815C10.3575 8.61138 11.0273 8.24871 11.7586 8.14706L13.2481 11.1259L12.0257 12.9588C11.9614 13.0555 11.922 13.1665 11.911 13.282C11.8999 13.3976 11.9175 13.5141 11.9621 13.6212C12.6113 15.1644 13.8391 16.3921 15.3824 17.0412C15.4896 17.0859 15.6061 17.1034 15.7216 17.0924C15.8371 17.0813 15.9482 17.0419 16.0449 16.9776L17.878 15.7553L20.857 17.2447C20.7554 17.9759 20.3927 18.6457 19.8359 19.1305C19.279 19.6152 18.5657 19.8823 17.8274 19.8824ZM15.0036 2C12.9231 1.99956 10.8783 2.53991 9.06975 3.56804C7.26117 4.59617 5.75094 6.07675 4.68724 7.86454C3.62354 9.65233 3.0429 11.6859 3.00229 13.7658C2.96168 15.8456 3.46249 17.9003 4.45559 19.7282L3.08841 23.8306C2.99162 24.1208 2.97757 24.4322 3.04785 24.73C3.11812 25.0277 3.26993 25.3 3.48626 25.5163C3.7026 25.7326 3.97491 25.8844 4.27267 25.9547C4.57044 26.0249 4.88189 26.0109 5.17212 25.9141L9.27484 24.5471C10.8808 25.4185 12.6647 25.9118 14.4903 25.9892C16.3159 26.0667 18.135 25.7262 19.8091 24.9939C21.4831 24.2616 22.9679 23.1567 24.15 21.7635C25.3322 20.3704 26.1806 18.7256 26.6305 16.9548C27.0804 15.184 27.12 13.3338 26.7462 11.5454C26.3725 9.75691 25.5952 8.07741 24.4737 6.63494C23.3523 5.19246 21.9162 4.0251 20.275 3.22186C18.6338 2.41861 16.8308 2.00071 15.0036 2ZM15.0036 24.5882C13.1422 24.5887 11.3135 24.0985 9.70194 23.1671C9.5945 23.1053 9.47288 23.0725 9.34896 23.0718C9.273 23.0722 9.19758 23.0845 9.12541 23.1082L4.7262 24.5741C4.68474 24.5879 4.64025 24.59 4.59771 24.5799C4.55517 24.5699 4.51627 24.5482 4.48536 24.5173C4.45446 24.4864 4.43277 24.4475 4.42273 24.405C4.41269 24.3624 4.4147 24.3179 4.42853 24.2765L5.89454 19.8824C5.92626 19.7874 5.93742 19.6868 5.92728 19.5872C5.91713 19.4876 5.88592 19.3913 5.83571 19.3047C4.66767 17.2872 4.19818 14.9406 4.50005 12.629C4.80193 10.3174 5.85831 8.17007 7.5053 6.5201C9.1523 4.87012 11.2978 3.80976 13.609 3.50352C15.9202 3.19728 18.2679 3.66229 20.2879 4.82639C22.3078 5.99049 23.887 7.78862 24.7806 9.9418C25.6742 12.095 25.8321 14.4829 25.2299 16.7349C24.6277 18.987 23.299 20.9775 21.45 22.3974C19.601 23.8174 17.335 24.5875 15.0036 24.5882Z" fill="white"/>
                </svg>
            </a>
            <a href="#" class="story-btn repost_send" data-story-id="{{$data['story_id']}}" style="display: none">ОТПРАВИТЬ</a>
            <div class="gap-2 align-items-center justify-content-center repost_done" style="display: none">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="10" cy="10" r="9.5" fill="#00F0FF" stroke="#00F0FF"/>
                    <path d="M4.75 10.75L8.25 14.25L15.25 6.75" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Готово</span>
            </div>
        </div>
    </div>
</div>
<div class="story-offcanvas" id="storydonate">
    <div class="story-offcanvas__head">
        <button class="story-offcanvas__close"></button>
    </div>

    <div class="story-offcanvas__footer">
        <form action="#" class="story-donate-form">
            <div class="story-donate-list">
                <label>
                    <input type="radio" name="donate" value="100">
                    <span class="story-donate-sum">100
                    <img src="/dist/img/deels_cur.svg" class="small_coin">
                    </span>
                </label>
                <label>
                    <input type="radio" name="donate" checked value="500">
                    <span class="story-donate-sum">500
                     <img src="/dist/img/deels_cur.svg" class="small_coin">
                    </span>
                </label>
                <label>
                    <input type="radio" name="donate" value="700">
                    <span class="story-donate-sum">700
                         <img src="/dist/img/deels_cur.svg" class="small_coin">
                    </span>
                </label>
                <label>
                    <input type="radio" name="donate" value="1000">
                    <span class="story-donate-sum">1000
                        <img src="/dist/img/deels_cur.svg" class="small_coin">
                    </span>
                </label>
                <label>
                    <input type="radio" name="donate" value="custom">
                    <span class="story-donate-sum">Ввести свою сумму</span>
                </label>
            </div>
            <input type="number" name="donate_amount" class="story-donate-input donate_amount" value="500" placeholder="Введите сумму доната">
            <button type="submit" class="btn btn_fill btn_flex donate_to_story"  data-route="{{route('stories.pay', ['id' => $data['story_id'], 'user_id' => $auth_user->id ?? Auth::user()->id ?? null])}}" data-story="{{$data['story_id']}}">Задонатить 500 <img src="/dist/img/deels_cur.svg" class="small_coin"></button>
        </form>
    </div>
</div>
@endif
