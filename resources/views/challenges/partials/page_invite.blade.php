@php
    $invitePermissions = app(\App\Services\Contests\ContestInvitationService::class)
        ->permissions($contest, $contestType, Auth::id());
    $invitedIds = app(\App\Services\Contests\ContestInvitationService::class)
        ->ids($contest->invite_user_ids ?? []);
@endphp

@if($invitePermissions['allowed'])
    <div
        class="challenge-invite-block contest-page-invite {{!empty($inviteWithShare) ? 'contest-page-invite--with-share' : ''}} {{!empty($inviteExpanded) ? 'is-open' : ''}}"
        data-expanded="{{!empty($inviteExpanded) ? '1' : '0'}}"
        data-users-url="{{route('contests.invites.users', ['type' => $contestType, 'id' => $contest->id])}}"
        data-store-url="{{route('contests.invites.store', ['type' => $contestType, 'id' => $contest->id])}}"
        data-invited-ids="{{implode(',', $invitedIds)}}"
        data-friends-only="{{$invitePermissions['friends_only'] ? '1' : '0'}}"
    >
        <style>
            .contest-page-invite { margin-top: 1.5rem; }
            .contest-page-invite__actions {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 3.4rem;
                gap: .5rem !important;
                align-items: stretch;
            }
            .contest-page-invite--with-share .contest-page-invite__actions {
                grid-template-columns: 3.4rem minmax(0, 1fr) 3.4rem;
                gap: 10px !important;
            }
            .contest-page-invite__actions .challenge-btn {
                width: 100%;
                min-width: 0;
                max-width: none;
                height: 3.4rem;
            }
            .contest-page-invite__actions .add_invite {
                grid-column: 2;
                width: 3.4rem !important;
                min-width: 3.4rem !important;
                height: 3.4rem !important;
                min-height: 3.4rem !important;
                padding: 0 !important;
                aspect-ratio: 1;
            }
            .contest-page-invite--with-share .contest-page-invite__actions .add_invite {
                grid-column: 3;
            }
            .contest-page-invite__submit {
                grid-column: 1;
                grid-row: 1;
                margin-top: 0;
            }
            .contest-page-invite--with-share .contest-page-invite__submit {
                grid-column: 2;
                width: calc(100% - 10px);
                margin-left: 10px;
            }
            .contest-page-invite--with-share .contest-action-small {
                grid-column: 1;
                grid-row: 1;
            }
            .contest-page-invite__submit[disabled] { cursor: default; opacity: .45; }
            .contest-page-invite__message {
                display: none;
                min-height: 1.25rem;
                margin-top: .5rem;
                font-size: .875rem;
            }
            .contest-page-invite .challenge-invite-search {
                display: block;
                width: 100%;
                min-height: 50px;
                margin: .5rem 0 12px;
                padding: 10px;
                appearance: none;
                color: #fff;
                background-color: transparent;
                border: 1px solid rgba(255, 255, 255, .2);
                border-radius: 5px;
                font-size: 14px;
            }
            .contest-page-invite .challenge-invite-search::placeholder {
                color: rgba(255, 255, 255, .5);
            }
            .contest-page-invite[data-expanded="1"] .add_invite { display: none; }
            .contest-page-invite .challenge-invite-selected-wrap {
                width: 100%;
                margin-top: .5rem;
                display: none;
            }
            .contest-page-invite .challenge-invite-selected-wrap.has-selected { display: block; }
        </style>
        <div class="challenge-invite-head contest-page-invite__actions">
            @if(!empty($inviteWithShare))
                <button class="story__button story__button_share-link story__button-purple btn__copy contest-action-small"
                        type="button" aria-label="Поделиться" title="Поделиться"></button>
            @endif
            <button class="challenge-btn challenge-btn--fill contest-page-invite__submit" type="button" disabled>
                Пригласить
            </button>
            <button class="challenge-btn challenge-btn--outline add_invite" type="button" aria-label="Выбрать пользователей">+</button>
        </div>
        <div class="challenge-invite-selected-wrap">
            <div class="challenge-invite-selected"></div>
        </div>

        <div class="challenge-invite-dropdown">
            <input type="text" class="challenge-invite-search" placeholder="Поиск">

            <div class="challenge-invite-section" data-section="friends">
                <button class="challenge-invite-section__title" type="button" aria-expanded="true">
                    <span>Друзья</span>
                    <span class="challenge-invite-section__arrow"></span>
                </button>
                <div class="challenge-invite-list" data-list="friends"></div>
            </div>

            @if(!$invitePermissions['friends_only'])
                <div class="challenge-invite-section" data-section="random">
                    <button class="challenge-invite-section__title" type="button" aria-expanded="true">
                        <span>Случайные пользователи</span>
                        <span class="challenge-invite-section__arrow"></span>
                    </button>
                    <div class="challenge-invite-list" data-list="random"></div>
                </div>
            @endif

            <div class="challenge-invite-section d-none" data-section="results">
                <button class="challenge-invite-section__title" type="button">
                    <span>Результаты поиска</span>
                </button>
                <div class="challenge-invite-list" data-list="results"></div>
            </div>
        </div>
        <p class="contest-page-invite__message" aria-live="polite"></p>
    </div>
@elseif(!empty($inviteWithShare))
    <button class="story__button story__button_share-link story__button-purple btn__copy contest-action-small"
            type="button" aria-label="Поделиться" title="Поделиться"></button>
@endif

@once
    @push('after_scripts')
        <script>
            (function ($) {
                function escapeHtml(value) {
                    return $('<div>').text(value || '').html();
                }

                function state(block) {
                    var value = block.data('pageInviteState');
                    if (!value) {
                        var invited = {};
                        $.each(String(block.attr('data-invited-ids') || '').split(','), function (_, id) {
                            if (id) invited[String(id)] = true;
                        });
                        value = {users: {}, selected: {}, invited: invited, loaded: false, timer: null};
                        block.data('pageInviteState', value);
                    }
                    return value;
                }

                function renderList(block, name, users) {
                    var current = state(block);
                    var list = block.find('[data-list="' + name + '"]').empty();
                    if (!users || !users.length) {
                        list.append('<p class="challenge-invite-empty">Нет пользователей</p>');
                        return;
                    }
                    $.each(users, function (_, user) {
                        var id = String(user.id);
                        current.users[id] = user;
                        list.append(
                            '<button class="challenge-invite-user ' +
                            (current.selected[id] ? 'is-selected ' : '') +
                            (current.invited[id] ? 'is-locked' : '') +
                            '" type="button" data-user-id="' + id + '">' +
                            '<img src="' + escapeHtml(user.avatar) + '" alt="">' +
                            '<span>' + escapeHtml(user.username || user.name || ('user_' + id)) + '</span></button>'
                        );
                    });
                }

                function sync(block) {
                    var current = state(block);
                    var selected = block.find('.challenge-invite-selected').empty();
                    $.each(current.selected, function (id, user) {
                        selected.append(
                            '<span class="challenge-invite-chip" data-user-id="' + id + '">' +
                            '<img src="' + escapeHtml(user.avatar) + '" alt="">' +
                            '<button type="button" class="challenge-invite-chip__remove" aria-label="Удалить">×</button></span>'
                        );
                    });
                    block.find('.challenge-invite-selected-wrap')
                        .toggleClass('has-selected', Object.keys(current.selected).length > 0);
                    block.find('.challenge-invite-user').each(function () {
                        var id = String($(this).attr('data-user-id'));
                        $(this).toggleClass('is-selected', !!current.selected[id]);
                        $(this).toggleClass('is-locked', !!current.invited[id]);
                    });
                    block.find('.contest-page-invite__submit').prop('disabled', !Object.keys(current.selected).length);
                    window.requestAnimationFrame(function () {
                        updateSelectedOverflow(block);
                    });
                }

                function updateSelectedOverflow(block) {
                    var wrap = block.find('.challenge-invite-selected-wrap');
                    var selected = block.find('.challenge-invite-selected');
                    var chips = selected.find('.challenge-invite-chip');
                    var maxWidth = wrap.innerWidth();

                    selected.find('.challenge-invite-more').remove();
                    chips.removeClass('is-overflow-hidden');

                    if (!chips.length || !maxWidth) return;

                    var more = $('<span class="challenge-invite-more">').text('еще ' + chips.length).appendTo(selected);
                    var moreWidth = more.outerWidth(true) || 56;
                    var used = 0;
                    var hiddenCount = 0;

                    chips.each(function () {
                        var chip = $(this);
                        var chipWidth = chip.outerWidth(true) + 8;
                        if (used + chipWidth + moreWidth > maxWidth) {
                            chip.addClass('is-overflow-hidden');
                            hiddenCount++;
                        } else {
                            used += chipWidth;
                        }
                    });

                    if (hiddenCount) {
                        more.text('еще ' + hiddenCount);
                    } else {
                        more.remove();
                    }
                }

                function load(block, query) {
                    var current = state(block);
                    return $.get(block.attr('data-users-url'), {q: query || ''}).done(function (response) {
                        if (!response || !response.success) return;
                        if (query) {
                            block.find('[data-section="friends"], [data-section="random"]').addClass('d-none');
                            block.find('[data-section="results"]').removeClass('d-none');
                            renderList(block, 'results', response.results || []);
                        } else {
                            block.find('[data-section="friends"], [data-section="random"]').removeClass('d-none');
                            block.find('[data-section="results"]').addClass('d-none');
                            renderList(block, 'friends', response.friends || []);
                            renderList(block, 'random', response.random || []);
                        }
                        current.loaded = true;
                        sync(block);
                    });
                }

                $('.contest-page-invite[data-expanded="1"]').each(function () {
                    load($(this), '');
                });

                var inviteResizeTimer;
                $(window).on('resize.pageInviteSelected', function () {
                    window.clearTimeout(inviteResizeTimer);
                    inviteResizeTimer = window.setTimeout(function () {
                        $('.contest-page-invite').each(function () {
                            updateSelectedOverflow($(this));
                        });
                    }, 100);
                });

                $(document).on('click', '.contest-page-invite .add_invite', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var block = $(this).closest('.contest-page-invite');
                    block.toggleClass('is-open');
                    if (block.hasClass('is-open') && !state(block).loaded) load(block, '');
                });

                $(document).on('click', '.contest-page-invite .challenge-invite-section__title', function (event) {
                    var section = $(this).closest('.challenge-invite-section');
                    var sectionName = section.attr('data-section');
                    if (sectionName !== 'friends' && sectionName !== 'random') return;

                    event.preventDefault();
                    section.toggleClass('is-collapsed');
                    $(this).attr('aria-expanded', section.hasClass('is-collapsed') ? 'false' : 'true');
                });

                $(document).on('click', '.contest-page-invite.is-open', function (event) {
                    if ($(this).attr('data-expanded') === '1') {
                        return;
                    }
                    if ($(event.target).closest(
                        '.challenge-invite-dropdown, .add_invite, .challenge-invite-selected'
                    ).length) {
                        return;
                    }

                    $(this).removeClass('is-open');
                });

                $(document).on('click', '.contest-page-invite .challenge-invite-user', function () {
                    var block = $(this).closest('.contest-page-invite');
                    var current = state(block);
                    var id = String($(this).attr('data-user-id'));
                    if (current.invited[id] || !current.users[id]) return;
                    if (current.selected[id]) delete current.selected[id];
                    else current.selected[id] = current.users[id];
                    sync(block);
                });

                $(document).on('click', '.contest-page-invite .challenge-invite-chip__remove', function () {
                    var block = $(this).closest('.contest-page-invite');
                    delete state(block).selected[String($(this).closest('.challenge-invite-chip').attr('data-user-id'))];
                    sync(block);
                });

                $(document).on('input', '.contest-page-invite .challenge-invite-search', function () {
                    var block = $(this).closest('.contest-page-invite');
                    var current = state(block);
                    var query = $(this).val().trim();
                    clearTimeout(current.timer);
                    current.timer = setTimeout(function () { load(block, query); }, 300);
                });

                $(document).on('click', '.contest-page-invite__submit', function () {
                    var button = $(this);
                    var block = button.closest('.contest-page-invite');
                    var current = state(block);
                    var ids = Object.keys(current.selected);
                    if (!ids.length || button.prop('disabled')) return;
                    button.prop('disabled', true);
                    block.find('.contest-page-invite__message').text('');
                    $.ajax({
                        type: 'POST',
                        url: block.attr('data-store-url'),
                        data: {_token: '{{csrf_token()}}', user_ids: ids}
                    }).done(function (response) {
                        $.each(response.invited_ids || [], function (_, id) { current.invited[String(id)] = true; });
                        current.selected = {};
                        if (block.attr('data-expanded') !== '1') {
                            block.removeClass('is-open');
                        }
                        block.find('.contest-page-invite__message').show().text('Приглашения отправлены');
                        sync(block);
                    }).fail(function (xhr) {
                        var errors = xhr.responseJSON && xhr.responseJSON.errors;
                        var message = errors && errors.user_ids ? errors.user_ids[0] : 'Не удалось отправить приглашения';
                        block.find('.contest-page-invite__message').text(message);
                        sync(block);
                    });
                });

                $(document).on('click', function (event) {
                    if (!$(event.target).closest('.contest-page-invite').length) {
                        $('.contest-page-invite[data-expanded!="1"]').removeClass('is-open');
                    }
                });
            })(jQuery);
        </script>
    @endpush
@endonce
