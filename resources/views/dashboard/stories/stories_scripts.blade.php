<link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet">
<script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
<script src="https://unpkg.com/@videojs/http-streaming@2.16.0/dist/videojs-http-streaming.min.js"></script>
<style>
    /* Add this to your stylesheet */
    .ios-play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .ios-play-button {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .ios-play-button svg {
        width: 30px;
        height: 30px;
        fill: white;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the video element
        const videoElement = document.getElementById('videoPlayer');
        if (!videoElement) {
            return;
        }

        // Extract the video URL from src attribute
        const videoUrl = videoElement.getAttribute('src');
        if (!videoUrl) {
            return;
        }

        // Initialize the player with iOS compatibility
        initVideoPlayer('videoPlayer', videoUrl);
    });

    function initVideoPlayer(videoElementId, videoUrl) {
        // Detect iOS
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isHLS = videoUrl.includes('.m3u8');

        if (isIOS) {
            // iOS native handling
            const videoEl = document.getElementById(videoElementId);

            // Ensure proper attributes for iOS
            videoEl.setAttribute('playsinline', '');
            videoEl.setAttribute('webkit-playsinline', '');
            videoEl.setAttribute('controls', '');

            // For HLS on iOS, let native player handle it
            if (isHLS) {
                videoEl.innerHTML = '';
                const source = document.createElement('source');
                source.src = videoUrl;
                source.type = 'application/vnd.apple.mpegurl';
                videoEl.appendChild(source);
            }

            videoEl.load();

            // Return mock player API for consistency
            return {
                play: () => videoEl.play(),
                pause: () => videoEl.pause(),
                dispose: () => {
                    videoEl.pause();
                    videoEl.removeAttribute('src');
                    videoEl.load();
                }
            };
        }

        // Video.js setup for non-iOS
        const player = videojs(videoElementId, {
            techOrder: ['html5'],
            autoplay: true,
            muted: false,
            playsinline: true,
            controls: true,
            preload: 'auto',
            html5: {
                vhs: {
                    overrideNative: !isHLS, // Don't override for HLS
                    withCredentials: false,
                    enableLowInitialPlaylist: true
                }
            }
        });

        player.src({
            src: videoUrl,
            type: isHLS ? 'application/x-mpegURL' : 'video/mp4'
        });

        // Handle iOS-like tap to play
        player.on('tap', function() {
            if (player.paused()) {
                player.play();
            } else {
                player.pause();
            }
        });

        player.on('error', function() {
            const error = player.error();
            console.error('VideoJS Error:', error);
            // Implement your error handling here
        });

        return player;
    }

    function ensureStoryVideoPlayback(videoPlayer, player) {
        if (!videoPlayer) {
            return;
        }

        function showManualPlayButton(playHandler) {
            var $media = $('#story-popup .popup-story-content');
            if (!$media.length || $media.find('.story-manual-play').length) {
                return;
            }

            var $button = $('<button type="button" class="story-manual-play play-btn copystories-btn" aria-label="Воспроизвести сторис"></button>');
            $button.css({
                position: 'absolute',
                top: '50%',
                left: '50%',
                transform: 'translate(-50%, -50%)',
                zIndex: 25,
                display: 'block',
                cursor: 'pointer'
            });

            $button.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $button.remove();
                playHandler();
            });

            $media.append($button);
        }

        function playWithNativeVideo() {
            videoPlayer.muted = false;
            videoPlayer.removeAttribute('muted');
            videoPlayer.setAttribute('playsinline', '');
            videoPlayer.setAttribute('webkit-playsinline', '');

            var playPromise = videoPlayer.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {
                    showManualPlayButton(playWithNativeVideo);
                });
            }
        }

        function playWithVideoJs() {
            player.muted(false);
            var playPromise = player.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {
                    showManualPlayButton(playWithVideoJs);
                });
            }
        }

        if (player && typeof player.ready === 'function') {
            player.ready(playWithVideoJs);
            return;
        }

        playWithNativeVideo();
    }

    window.storyModalNavigator = (function () {
        var userId = '{{Auth::user()->id ?? null}}';
        var catalogUrl = '{{ route('stories.catalog') }}';
        var previewBaseUrl = '{{ url('/api/stories/preview') }}';
        var ids = [];
        var idIndex = {};
        var currentIndex = -1;
        var hasMore = false;
        var nextPage = 2;
        var initialized = false;
        var isLoading = false;
        var loadingPromise = null;

        function isCatalogPage() {
            var path = (window.location.pathname || '').replace(/\/+$/, '');
            return path === '/stories';
        }

        function appendIds(newIds) {
            if (!Array.isArray(newIds)) {
                return;
            }

            newIds.forEach(function (rawId) {
                var id = parseInt(rawId, 10);
                if (!Number.isFinite(id) || idIndex[id] === true) {
                    return;
                }
                idIndex[id] = true;
                ids.push(id);
            });
        }

        function collectInitialIds() {
            var pageIds = [];
            $('#stories-list .show_story[data-story], .copystories-list.catalog-list .show_story[data-story]').each(function () {
                var id = parseInt($(this).attr('data-story'), 10);
                if (Number.isFinite(id)) {
                    pageIds.push(id);
                }
            });
            return pageIds;
        }

        function ensureInit() {
            if (initialized) {
                return isCatalogPage();
            }

            if (!isCatalogPage()) {
                initialized = true;
                return false;
            }

            var state = window.storyCatalogPagination || {};
            var currentPage = parseInt(state.currentPage, 10);
            if (!Number.isFinite(currentPage) || currentPage < 1) {
                currentPage = 1;
            }

            hasMore = !!state.hasMore;
            nextPage = currentPage + 1;
            appendIds(collectInitialIds());
            initialized = true;
            return true;
        }

        function buildPreviewRoute(storyId) {
            var route = previewBaseUrl + '/' + storyId;
            var params = new URLSearchParams();

            if (userId) {
                params.set('user_id', userId);
            }

            params.set('_', Date.now().toString());
            route += '?' + params.toString();

            return route;
        }

        function parseIdsFromResponse(response) {
            var loadedIds = [];
            if (response && Array.isArray(response.data)) {
                response.data.forEach(function (item) {
                    var id = parseInt(item && item.id, 10);
                    if (Number.isFinite(id)) {
                        loadedIds.push(id);
                    }
                });
                return loadedIds;
            }

            if (response && response.html) {
                var $tmp = $('<div>').html(response.html);
                $tmp.find('[data-story]').each(function () {
                    var id = parseInt($(this).attr('data-story'), 10);
                    if (Number.isFinite(id)) {
                        loadedIds.push(id);
                    }
                });
            }
            return loadedIds;
        }

        function fetchNextIds() {
            if (isLoading) {
                return loadingPromise;
            }

            var filters = new URLSearchParams(window.location.search || '');
            var requestData = { page: nextPage };
            filters.forEach(function (value, key) {
                if (key !== 'page') {
                    requestData[key] = value;
                }
            });

            if (ids.length) {
                requestData.exclude_ids = ids.join(',');
            }

            isLoading = true;
            loadingPromise = new Promise(function (resolve) {
                $.ajax({
                    type: 'GET',
                    url: catalogUrl,
                    dataType: 'json',
                    data: requestData,
                    success: function (response) {
                        if (response && response.success) {
                            var beforeCount = ids.length;
                            appendIds(parseIdsFromResponse(response));
                            hasMore = !!response.has_more;
                            if (ids.length === beforeCount) {
                                hasMore = false;
                            }
                            nextPage += 1;
                        } else {
                            hasMore = false;
                        }
                    },
                    error: function () {
                        hasMore = false;
                    },
                    complete: function () {
                        isLoading = false;
                        loadingPromise = null;
                        resolve();
                    }
                });
            });

            return loadingPromise;
        }

        function setCurrentStory(storyId) {
            var id = parseInt(storyId, 10);
            if (!Number.isFinite(id)) {
                return;
            }

            if (ensureInit()) {
                appendIds([id]);
                currentIndex = ids.indexOf(id);
            }
            updateControls();
        }

        function updateControls() {
            var $prev = $('#story-popup .story-nav-prev');
            var $next = $('#story-popup .story-nav-next');
            if (!$prev.length || !$next.length) {
                return;
            }

            if (!ensureInit()) {
                $prev.addClass('disabled');
                $next.addClass('disabled');
                return;
            }

            var hasPrev = currentIndex > 0;
            var hasNext = currentIndex >= 0 && (currentIndex < ids.length - 1 || hasMore);
            $prev.toggleClass('disabled', !hasPrev);
            $next.toggleClass('disabled', !hasNext);
        }

        function openStoryById(storyId) {
            var popupInstance = $.magnificPopup.instance;
            if (popupInstance && popupInstance.isOpen) {
                $.magnificPopup.close();
            }

            return $.ajax({
                type: 'GET',
                url: buildPreviewRoute(storyId),
                success: function (data) {
                    if (data && data.success) {
                        showStory(data.data);
                    } else if (data && data.error) {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> ' + data.error + '</div>');
                    }
                }
            });
        }

        async function goNext() {
            if (!ensureInit() || currentIndex < 0) {
                updateControls();
                return;
            }

            if (currentIndex < ids.length - 1) {
                await openStoryById(ids[currentIndex + 1]);
                return;
            }

            if (!hasMore) {
                updateControls();
                return;
            }

            await fetchNextIds();
            if (currentIndex < ids.length - 1) {
                await openStoryById(ids[currentIndex + 1]);
            } else {
                updateControls();
            }
        }

        async function goPrev() {
            if (!ensureInit() || currentIndex <= 0) {
                updateControls();
                return;
            }
            await openStoryById(ids[currentIndex - 1]);
        }

        return {
            setCurrentStory: setCurrentStory,
            updateControls: updateControls,
            syncFromModal: function () {
                var currentStoryId = parseInt($('#story-popup .popup-story-content').attr('data-story-id'), 10);
                if (Number.isFinite(currentStoryId)) {
                    setCurrentStory(currentStoryId);
                } else {
                    updateControls();
                }
            },
            goNext: goNext,
            goPrev: goPrev
        };
    })();

    $('body').on('click', '.closebtn',function (e) {
        $(this).parents('.alert').remove();
    });

    function showSuspiciousActionError(xhr) {
        if (!xhr || xhr.status !== 403) {
            return false;
        }

        var restriction = xhr.responseJSON || {};
        var shown = false;
        if ($.magnificPopup && $.magnificPopup.instance && $.magnificPopup.instance.isOpen) {
            $.magnificPopup.close();
        }
        if (window.showSuspiciousRestriction) {
            shown = window.showSuspiciousRestriction(restriction);
        }
        if (!shown) {
            var modalId = restriction.shouldShowEmailPrompt
                ? '#select-email-modal'
                : (restriction.shouldShowPhonePrompt ? '#select-phone-modal' : '#suspicious-activity-modal');
            var modal = $(modalId);
            if (modal.length) {
                if (modalId === '#select-phone-modal' && $.fn.mask) {
                    modal.find('#select-phone').mask('+7 (999) 999-99-99');
                }
                if (modalId === '#suspicious-activity-modal') {
                    modal.find('#suspicious-activity-message').text(restriction.message || restriction.error || 'Действие временно недоступно');
                }
                modal.css('display', 'flex');
                $('body').addClass('overflow');
                setTimeout(function () {
                    modal.find('#select-email-code:visible, #select-phone-code:visible, #select-email:not([readonly]), #select-phone:not([readonly]), button:visible').first().trigger('focus');
                }, 100);
                shown = true;
            }
        }
        if (!shown) {
            window.alert(restriction.message || restriction.error || 'Действие временно недоступно');
        }

        return shown;
    }

    $('body').on('click', '.add_like', function (e) {
        e.preventDefault();
        var like_btn = $(this);
        var route = $(this).attr('data-route');
        var story_id = $(this).attr('data-story');
        like_btn.toggleClass('active');
        $.ajax({
            type: 'POST',
            url: route,
            data: {user_id: '{{Auth::user()->id ?? null}}', story_id: story_id},
            success: function (data) {
                if(data.success) {

                } else {
                    like_btn.toggleClass('active');
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.errors+'</div>')
                }
            },
            error: function (xhr) {
                like_btn.toggleClass('active');
                showSuspiciousActionError(xhr);
            }
        });
    });
     $('body').on('click', '.add_comment_like', function (e) {
        e.preventDefault();
        var like_btn = $(this);
        var route = '{{route('stories.comment.like.web')}}';
        var comment_id = $(this).attr('data-comment-id');
        like_btn.toggleClass('active');
        $.ajax({
            type: 'POST',
            url: route,
            data: {user_id: '{{Auth::user()->id ?? null}}', comment_id: comment_id},
            success: function (data) {
                if(data.count > 0) {
                    like_btn.find('.count').text(data.count).show();
                } else {
                    like_btn.find('.count').hide();
                }
            },
            error: function (xhr) {
                like_btn.toggleClass('active');
                showSuspiciousActionError(xhr);
            }
        });
    });

    $('body').on('click', '.donate_to_story', function (e) {
        e.preventDefault();
        var route = $(this).attr('data-route');
        var story_id = $(this).attr('data-story');
        var amount = $(this).parents('form').find('.donate_amount').val();
        $.ajax({
            type: 'POST',
            url: route,
            data: {user_id: '{{Auth::user()->id ?? null}}', story_id: story_id, amount:amount},
            success: function (data) {
                if(data.success) {
                    $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Успешно!</div>');
                    $('.story-offcanvas__close').trigger('click');
                } else {
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.error+'</div>')
                }
            },
            error: function (xhr) {
                showSuspiciousActionError(xhr);
            }
        });
    });



    $('body').on('click', '.post_comment', function (e) {
        e.preventDefault();
        var story_id = $(this).attr('data-story');
        var comment = $('body').find('[name="story_comment"]').val();
        var user_name = '{{\Illuminate\Support\Facades\Auth::user()->fullname ?? null}}';
        var user_avatar = '{{\Illuminate\Support\Facades\Auth::user()->avatar_url ?? null}}';
        $.ajax({
            type: 'POST',
            url: '{{route('stories.comment.web')}}',
            data: {user_id: '{{Auth::user()->id ?? null}}', story_id: story_id, comment:comment},
            success: function (data) {
                if(data.success) {
                    $('.no_comments').remove();
                    var comment_view = '<div class="story-comment__item"> <img src="'+user_avatar+'" alt="" class="story-comment__avatar"> <div class="story-comment__content"> <div class="story-comment__name">'+user_name+'</div> <div class="story-comment__text">'+comment+'</div><button class="story-aside__btn add_comment_like" data-comment-id="'+data.comment_id+'"> <svg width="26" height="21" viewBox="0 0 26 21" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M7.9375 1C4.52031 1 1.75 3.6902 1.75 7.00857C1.75 13.0171 9.0625 18.4795 13 19.75C16.9375 18.4795 24.25 13.0171 24.25 7.00857C24.25 3.6902 21.4797 1 18.0625 1C15.97 1 14.1194 2.00889 13 3.5531C12.4294 2.7639 11.6715 2.11983 10.7902 1.67541C9.90901 1.23099 8.93048 0.99932 7.9375 1Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </svg> </button> </div> </div>';
                    $('body').find('.story-comment__list').append(comment_view);
                    $('body').find('[name="story_comment"]').val('');
                    setTimeout(function() {
                        $('body').find('.story-comment').animate({scrollTop: 9999});
                    }, 100);
                } else {
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.errors+'</div>')
                }
            },
            error: function (xhr) {
                showSuspiciousActionError(xhr);
            }
        });
    });

    $('body').on('click', '.story-autor__subinfo', function (e) {
        $(this).toggleClass('show')
    })

    $('body').on('click', '.donate_story', function (e) {
        e.preventDefault();
        var type = $(this).attr('data-type');
        var story = $(this);
        var route = $(this).attr('data-route');
        var id = $(this).attr('data-story');
        var paid = $(this).attr('data-paid');
        var amount = $(this).attr('data-amount');
        $.ajax({
            type: 'POST',
            url: route,
            data: {user_id: '{{Auth::user()->id ?? null}}', story_id: id},
            success: function (data) {
                if(data.success) {
                    $.magnificPopup.close();
                    showStory(data.data);
                } else {
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.error+'</div>')
                }
            }
        });
    });


    $('body').on('click', '[data-story-nav]',function (e) {
        e.preventDefault();
        var thTarget = $(this).attr('data-story-nav')
        $('.story-offcanvas').removeClass('active')
        $(thTarget).addClass('active')
    });

    $('body').on('click', '.story-offcanvas__close',function (e) {
        e.preventDefault();
        var thParent = $(this).closest('.story-offcanvas.active')
        $(thParent).removeClass('active')
    });

    $('body').on('click', '.show_story', function (e) {
        e.preventDefault();
        var route = $(this).attr('data-route');
        var id = $(this).attr('data-story');
        window.storyModalNavigator.setCurrentStory(id);

        $.ajax({
            type: 'GET',
            url: route,
            success: function (data) {
                if(data.success) {
                    showStory(data.data);
                } else {
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.error+'</div>')
                }
            }
        });
    });

    $('body').on('click', '.story-nav-prev', function (e) {
        e.preventDefault();
        window.storyModalNavigator.goPrev();
    });

    $('body').on('click', '.story-nav-next', function (e) {
        e.preventDefault();
        window.storyModalNavigator.goNext();
    });

    @if(isset($_GET['show']) && $_GET['show'])

        var story_item = '<div href="#story-popup" class="story_shared copystories-item show_story" data-route="{{url('/')}}/api/stories/preview/{{$_GET['show']}}?user_id={{\Illuminate\Support\Facades\Auth::user()->id ?? null}}" data-story="{{$_GET['show']}}" data-type="image" data-paid="0" data-amount=""> </a>'
        $('body').append(story_item);
        $('body').find('.show_story[data-story="{{$_GET['show']}}"]').trigger('click');
        var uri = window.location.toString();

        if (uri.indexOf("?") > 0) {
            var clean_uri = uri.substring(0, uri.indexOf("?"));
            window.history.replaceState({}, document.title, clean_uri);
        }
        $('body').find('.story_shared').remove();
    @endif

    function showStory(data) {
        $('.challenge-media video').each(function () {
            this.pause();
        });
        $('#story-popup .story-wrap').html(data);
        window.storyModalNavigator.syncFromModal();
        var popupInstance = $.magnificPopup.instance;
        if (popupInstance && popupInstance.isOpen) {
            $.magnificPopup.close();
        }

        setTimeout(function () {
            $.magnificPopup.open({
                items: {
                    src: $('#story-popup')
                },
                type:'inline',
                midClick: true,
                callbacks: {
                    open: function() {

                        const videoPlayer = document.getElementById('videoPlayer');
                        const videoEndpoint = videoPlayer ? videoPlayer.getAttribute('data-video') : null;

                        if(videoPlayer && videoEndpoint) {
                            var player = initVideoPlayer('videoPlayer', videoEndpoint);
                            ensureStoryVideoPlayback(videoPlayer, player);
                        } else {
                            var thVideo = this.content.find('video');
                            if(thVideo.length) {
                                ensureStoryVideoPlayback(thVideo[0]);
                            }
                        }
                    },
                    close: function() {
                        var thVideo = this.content.find('video')
                        if(thVideo.length) {
                            thVideo[0].pause()
                            thVideo[0].currentTime = 0
                        }
                    }
                }
            });
        }, 0);
    }

    $('body').on('change', '.story-donate-form input[type=radio]', function() {
        var customCostInput = $(this).closest('.story-donate-form').find('.story-donate-input');
        var thBtn = $(this).closest('.story-donate-form').find('button[type=submit]');
        if($(this).val() === 'custom') {
            thBtn.text('Задонатить')
            customCostInput
                .css('display', 'block')
                .attr('required', 'required')
                .focus()
                .val('')
            customCostInput.keydown(function(e) {
                var charCode = (e.which) ? e.which : e.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                }
                if($(this).val() === '') {
                    thBtn.text('Задонатить')
                } else {
                    thBtn.html('Задонатить ' + parseInt($(this).val()).toLocaleString() + ' <img src="/dist/img/deels_cur.svg" class="small_coin">')
                }
                thBtn.text('Задонатить')
            })
        } else {
            thBtn.html('Задонатить ' + $(this).val() + ' <img src="/dist/img/deels_cur.svg" class="small_coin">')
            customCostInput
                .css('display', 'none')
                .removeAttr('required')
                .val($(this).val())
        }
    });

    $('body').on('change', '.repost_users_checkbox', function() {
        if ($('.repost_users_checkbox:checked').length > 0) {
            $('.repost_btn').hide();
            $('.repost_send').show();
        } else {
            $('.repost_btn').show();
            $('.repost_send').hide();
        }
    });

    $('body').on('click', '.repost_send', function(e) {
        e.preventDefault();
        var checkedValues = $('input:checkbox[name="repost_users[]"]:checked').map(function() {
            return this.value;
        }).get();

        var story_id = $(this).attr('data-story-id');
        $.ajax({
            type: 'POST',
            url: '{{route('stories.repost')}}',
            data: {user_id: '{{Auth::user()->id ?? null}}', receivers: checkedValues, story_id: story_id},
            success: function (data) {
                console.log(data);
                if(data.success) {
                    $('input:checkbox[name="repost_users[]"]:checked').prop( "checked", false );
                    $('.repost_done').addClass('d-flex');
                    $('.repost_send').hide();
                    setTimeout(function() {
                        $('.repost_btn').show();
                        $('.repost_done').removeClass('d-flex');
                        $('.story-offcanvas').removeClass('active');
                    }, 2000);
                }
            }
        });
    });

    $(document).on("click", ".btn_url_copy", function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var temp = $("<input type='text' id='clipboard'>");
        $("body").append(temp);
        $('body').find('#clipboard').val(url).select();
        document.execCommand("copy");
        navigator.clipboard.writeText($('body').find('#clipboard').val())
            .then(function() {
                // Success message
                console.log('Text successfully copied to clipboard');
            })
            .catch(function(err) {
            });
        temp.remove();
        $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span> Ссылка скопирована в буфер обмена</div>')
    });
</script>
