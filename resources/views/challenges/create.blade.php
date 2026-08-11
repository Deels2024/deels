@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')
    @php
        $activeTab = $activeTab ?? (isset($battle) ? 'battle' : 'challenge');
        $isEditing = isset($challenge) || isset($battle);
    @endphp

    <div class="account__content">
        <div class="account-main">
            <div class="account-info">
                <section class="challenge pb-8">
                    @if ($errors->any())
                        <div style="margin-bottom: 30px">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li style="color: red;margin-bottom: 10px">{{$error}}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="challenge-create-tabs">
                        <span class="challenge-create-tabs__label">{{$isEditing ? 'Редактирование' : 'Создание'}}</span>
                        @if(!$isEditing || $activeTab === 'challenge')
                            <button class="challenge-create-tabs__tab {{$activeTab === 'challenge' ? 'active' : ''}}" type="button" data-create-tab="challenge" {{$isEditing ? 'disabled aria-disabled=true' : ''}}>челленджа</button>
                        @endif
                        @if(!$isEditing)
                            <span class="challenge-create-tabs__divider">/</span>
                        @endif
                        @if(!$isEditing || $activeTab === 'battle')
                            <button class="challenge-create-tabs__tab {{$activeTab === 'battle' ? 'active' : ''}}" type="button" data-create-tab="battle" {{$isEditing ? 'disabled aria-disabled=true' : ''}}>батла</button>
                        @endif
                    </div>

                    <div class="challenge-create-pane {{$activeTab === 'challenge' ? 'active' : ''}}" data-create-pane="challenge">
                        @include('challenges.partials.create_form', [
                            'mode' => 'challenge',
                            'entity' => $challenge ?? null,
                        ])
                    </div>

                    <div class="challenge-create-pane {{$activeTab === 'battle' ? 'active' : ''}}" data-create-pane="battle">
                        @include('challenges.partials.create_form', [
                            'mode' => 'battle',
                            'entity' => $battle ?? null,
                        ])
                    </div>
                </section>
            </div>
        </div>
    </div>
    @include('challenges.modal')
    @include('stories.modal')
@endsection

@section('page-js')
    <script>
        var sharedChallengeFieldsSyncing = false;
        var sharedChallengeInviteSyncing = false;
        var challengeCreateEditMode = @json($isEditing);
        var sharedChallengeFileStore = {};
        var challengeDraftRestoring = false;
        var challengeDraftSaveTimer = null;
        var challengeDraftKey = 'challenge_create_draft_v2';
        var challengeDraftSubmitKey = 'challenge_create_draft_submitted_v2';
        var challengeCreateHasOldInput = @json(session()->hasOldInput() || $errors->any() || session()->has('error'));
        var sharedChallengeFieldNames = [
            'title',
            'description',
            'date_from_visual',
            'date_to_visual',
            'mainImg',
            'intro_story',
            'days',
            'cost',
            'criteria[]',
            'visibility',
            'rhythm_visual',
            'reward_amount'
        ];

        function activeChallengeForm() {
            return $('.challenge-create-pane.active .challenge-form');
        }

        function isSharedChallengeField(field) {
            return $.inArray($(field).attr('name'), sharedChallengeFieldNames) !== -1;
        }

        function updateDescriptionCounter(textarea) {
            var field = $(textarea);
            var maxLength = parseInt(field.attr('data-max-length'), 10) || 5000;

            if (textarea.value.length > maxLength) {
                textarea.value = textarea.value.slice(0, maxLength);
            }

            field.siblings('.challenge-description-counter')
                .text('' + textarea.value.length + '/' + maxLength)
                .toggleClass('fulled', textarea.value.length >= maxLength);
        }

        $(document).on('input', '.challenge-form textarea[name="description"]', function () {
            updateDescriptionCounter(this);
        });

        $('.challenge-form textarea[name="description"]').each(function () {
            updateDescriptionCounter(this);
        });

        function escapeChallengeAttrValue(value) {
            return String(value || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
        }

        function copyFilesToInput(input, files) {
            if (!files || !files.length || typeof DataTransfer === 'undefined') {
                return false;
            }

            var transfer = new DataTransfer();
            $.each(files, function (_, file) {
                transfer.items.add(file);
            });
            input.files = transfer.files;
            return true;
        }

        function applySharedFilePreview(input) {
            var target = $(input);
            var container = target.attr('data-preview-container');
            var upload = target.closest('.challenge-upload');

            if (!container || !input.files || !input.files.length) {
                return;
            }

            readUrl(input, container);
            upload.find('.form-file-area').each(function () {
                this.style.setProperty('display', 'none', 'important');
            });
            $(container).show();
            upload.find('.upload_preview').addClass('active');
            upload.find('.preview_replace').show();
        }

        function syncSharedField(source) {
            if (sharedChallengeFieldsSyncing || !isSharedChallengeField(source)) {
                return;
            }

            var sourceField = $(source);
            var name = sourceField.attr('name');
            var sourceForm = sourceField.closest('.challenge-form');
            var targetForms = $('.challenge-form').not(sourceForm);
            var nameSelector = escapeChallengeAttrValue(name);

            sharedChallengeFieldsSyncing = true;

            if (sourceField.attr('type') === 'file') {
                sharedChallengeFileStore[name] = source.files ? Array.prototype.slice.call(source.files) : [];
                targetForms.each(function () {
                    var targetInput = $(this).find('[name="' + nameSelector + '"]')[0];
                    if (targetInput && copyFilesToInput(targetInput, sharedChallengeFileStore[name])) {
                        applySharedFilePreview(targetInput);
                    }
                });
            } else if (sourceField.is(':radio')) {
                targetForms.each(function () {
                    $(this).find('[name="' + nameSelector + '"][value="' + escapeChallengeAttrValue(sourceField.val()) + '"]').prop('checked', sourceField.is(':checked'));
                });
            } else if (sourceField.is(':checkbox')) {
                targetForms.each(function () {
                    $(this).find('[name="' + nameSelector + '"][value="' + escapeChallengeAttrValue(sourceField.val()) + '"]').prop('checked', sourceField.is(':checked'));
                });
            } else {
                targetForms.find('[name="' + nameSelector + '"]').not(':radio, :checkbox').each(function () {
                    var target = $(this);
                    var value = sourceField.val();
                    var picker = target.data('DateTimePicker');

                    target.val(value);
                    if (name === 'description') {
                        updateDescriptionCounter(this);
                    }
                    if (picker && (name === 'date_from_visual' || name === 'date_to_visual')) {
                        if (value) {
                            picker.date(moment(value, 'DD.MM.YY HH:mm'));
                        } else {
                            picker.clear();
                        }
                    }
                });
            }

            targetForms.each(function () {
                var form = $(this);
                validateRewardAmount(form);
                syncChallengeExtraBlocks(form);
            });

            sharedChallengeFieldsSyncing = false;
        }

        function syncSharedFieldsToPane(tab) {
            var sourceForm = activeChallengeForm();
            var targetForm = $('.challenge-create-pane[data-create-pane="' + tab + '"]').find('.challenge-form').first();

            if (!sourceForm.length || !targetForm.length || sourceForm[0] === targetForm[0]) {
                return;
            }

            sourceForm.find(':input:not(:disabled)').each(function () {
                if (isSharedChallengeField(this)) {
                    syncSharedField(this);
                }
            });
        }

        function draftFormMode(form) {
            return form.find('.challenge-invite-block').attr('data-mode') || 'challenge';
        }

        function serializeChallengeDraftForm(form) {
            var data = {};

            form.find(':input[name]').not('[type="file"]').each(function () {
                var field = $(this);
                var name = field.attr('name');

                if (!name || name === '_token' || field.prop('disabled')) {
                    return;
                }

                if (field.is(':radio')) {
                    if (field.is(':checked')) {
                        data[name] = field.val();
                    }
                    return;
                }

                if (field.is(':checkbox')) {
                    if (!data[name]) {
                        data[name] = [];
                    }
                    if (field.is(':checked')) {
                        data[name].push(field.val());
                    }
                    return;
                }

                if (name.slice(-2) === '[]') {
                    if (!data[name]) {
                        data[name] = [];
                    }
                    data[name].push(field.val());
                    return;
                }

                data[name] = field.val();
            });

            return data;
        }

        function applyChallengeDraftForm(form, values) {
            $.each(values || {}, function (name, value) {
                var fields = form.find('[name="' + escapeChallengeAttrValue(name) + '"]').not('[type="file"]');

                fields.each(function () {
                    var field = $(this);

                    if (field.is(':radio')) {
                        field.prop('checked', String(field.val()) === String(value));
                        return;
                    }

                    if (field.is(':checkbox')) {
                        field.prop('checked', $.isArray(value) && $.inArray(field.val(), value) !== -1);
                        return;
                    }

                    if (name.slice(-2) !== '[]') {
                        field.val(value);

                        if (name === 'description') {
                            updateDescriptionCounter(this);
                        }

                        var picker = field.data('DateTimePicker');
                        if (picker && (name === 'date_from_visual' || name === 'date_to_visual')) {
                            if (value) {
                                picker.date(moment(value, 'DD.MM.YY HH:mm'));
                            } else {
                                picker.clear();
                            }
                        }
                    }
                });
            });
        }

        function saveChallengeDraft() {
            if (challengeCreateEditMode || challengeDraftRestoring) {
                return;
            }

            var draft = {
                activeTab: $('.challenge-create-tabs__tab.active').attr('data-create-tab') || 'challenge',
                forms: {},
                invites: {},
                calls: {}
            };

            $('.challenge-form').each(function () {
                var form = $(this);
                var mode = draftFormMode(form);
                var inviteBlock = form.find('.challenge-invite-block');
                var callBlock = form.find('.challenge-call-block');

                draft.forms[mode] = serializeChallengeDraftForm(form);

                if (inviteBlock.length) {
                    draft.invites[mode] = getInviteState(inviteBlock).selected;
                }

                if (callBlock.length) {
                    draft.calls[mode] = getCallState(callBlock).selected;
                }
            });

            try {
                localStorage.setItem(challengeDraftKey, JSON.stringify(draft));
            } catch (e) {}
        }

        function scheduleChallengeDraftSave() {
            clearTimeout(challengeDraftSaveTimer);
            challengeDraftSaveTimer = setTimeout(saveChallengeDraft, 250);
        }

        function restoreChallengeDraft() {
            if (challengeCreateEditMode) {
                return;
            }

            var raw = null;
            var draft = null;

            try {
                if (sessionStorage.getItem(challengeDraftSubmitKey) && !challengeCreateHasOldInput) {
                    localStorage.removeItem(challengeDraftKey);
                    sessionStorage.removeItem(challengeDraftSubmitKey);
                    return;
                }

                raw = localStorage.getItem(challengeDraftKey);
            } catch (e) {
                return;
            }

            if (!raw) {
                return;
            }

            try {
                draft = JSON.parse(raw);
            } catch (e) {
                try {
                    localStorage.removeItem(challengeDraftKey);
                } catch (removeError) {}
                return;
            }

            challengeDraftRestoring = true;

            $('.challenge-form').each(function () {
                var form = $(this);
                var mode = draftFormMode(form);
                var inviteBlock = form.find('.challenge-invite-block');
                var callBlock = form.find('.challenge-call-block');
                var inviteUsers = (draft.invites && draft.invites[mode]) || {};
                var callUser = draft.calls && draft.calls[mode] ? draft.calls[mode] : null;

                applyChallengeDraftForm(form, draft.forms && draft.forms[mode] ? draft.forms[mode] : {});

                if (inviteBlock.length) {
                    inviteBlock.attr('data-selected-ids', Object.keys(inviteUsers).join(','));
                    inviteBlock.data('draftInviteUsers', inviteUsers);
                }

                if (callBlock.length && callUser && callUser.id) {
                    callBlock.attr('data-selected-id', callUser.id);
                    callBlock.data('draftCallUser', callUser);
                }
            });

            if (draft.activeTab) {
                $('.challenge-create-tabs__tab[data-create-tab="' + draft.activeTab + '"]').trigger('click');
            }

            $('.challenge-form').each(function () {
                var form = $(this);
                syncMinParticipants(form);
                validateParticipantsLimit(form);
                validateRewardAmount(form);
                syncChallengeExtraBlocks(form);
            });

            challengeDraftRestoring = false;
        }

        $(document).on('keydown', '.challenge-form', function (e) {
            if (e.key !== 'Enter' && e.which !== 13) {
                return;
            }

            if ($(e.target).is('textarea')) {
                return;
            }

            e.preventDefault();
            return false;
        });

        $('.challenge-create-tabs__tab').on('click', function () {
            if (challengeCreateEditMode || $(this).prop('disabled')) {
                return;
            }

            var tab = $(this).attr('data-create-tab');
            syncSharedFieldsToPane(tab);
            $('.challenge-create-tabs__tab').removeClass('active');
            $(this).addClass('active');
            $('.challenge-create-pane').removeClass('active');
            $('.challenge-create-pane[data-create-pane="' + tab + '"]').addClass('active');
            scheduleInviteSelectedOverflowUpdate();
            scheduleChallengeDraftSave();
        });

        $('.challenge-media--video:not(.show_story)').on('click', function (e) {
            e.preventDefault();
            var route = $(this).attr('data-route');

            $.ajax({
                type: 'GET',
                url: route,
                success: function (data) {
                    if(data.success) {
                        showChallenge(data.data);
                    } else {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.error+'</div>')
                    }
                }
            });
        });
    </script>

    <script src="{{ext_asset('/dist/js/validations.js')}}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="{{asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datetimepicker.min.js')}}"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="{{asset('assets/js/summernoteLang.js')}}"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>
        $('.filePreviewUploadMain, .filePreviewUploadStory').change(function () {
            var container = $(this).attr('data-preview-container');
            var upload = $(this).closest('.challenge-upload');
            readUrl(this, container);
            upload.find('.form-file-area').each(function () {
                this.style.setProperty('display', 'none', 'important');
            });
            $(container).show();
            upload.find('.upload_preview').addClass('active');
            upload.find('.preview_replace').show();
            syncSharedField(this);
        });

        $(document).on('click', '.challenge-upload .preview_replace', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var input = $(this).closest('.challenge-upload').find('input[type="file"]');
            input.val('');
            input.trigger('click');
        });

        $('.challenge-form').each(function () {
            var form = $(this);
            var dateRow = form.find('.challenge-date-row');
            var fromPicker = form.find('input[name="date_from_visual"]');
            var toPicker = form.find('input[name="date_to_visual"]');

            var datepickerOptions = {
                format: 'DD.MM.YY HH:mm',
                locale: 'ru',
                sideBySide: true,
                useCurrent: false,
                viewMode: 'days',
                widgetPositioning: {
                    horizontal: 'left',
                    vertical: 'bottom'
                },
                widgetParent: dateRow,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-angle-up',
                    down: 'fa fa-angle-down',
                    previous: 'fa fa-angle-left',
                    next: 'fa fa-angle-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash',
                    close: 'fa fa-times'
                },
                showClear: true,
                showClose: true
            };

            fromPicker.datetimepicker(datepickerOptions);
            toPicker.datetimepicker(datepickerOptions);

            function prepareDatepickerWidget() {
                var widget = dateRow.find('.bootstrap-datetimepicker-widget').addClass('challenge-datetimepicker');
                widget.find('.glyphicon-chevron-left, .glyphicon-chevron-right, .glyphicon-chevron-up, .glyphicon-chevron-down, .fa-angle-left, .fa-angle-right, .fa-angle-up, .fa-angle-down').text('');
                widget.find('[data-action="showHours"], [data-action="showMinutes"]').removeAttr('title alt');
                widget.find('.glyphicon-time').text('⌚');
                widget.find('.glyphicon-calendar').text('□');
                widget.find('.glyphicon-trash, .glyphicon-remove').text('×');
            }

            function keepDatepickerInDaysMode() {
                dateRow.find('.challenge-datetimepicker .datepicker-months, .challenge-datetimepicker .datepicker-years, .challenge-datetimepicker .datepicker-decades').hide();
                dateRow.find('.challenge-datetimepicker .datepicker-days').show();
            }

            fromPicker.on('dp.show', function () {
                prepareDatepickerWidget();
                keepDatepickerInDaysMode();
            });
            toPicker.on('dp.show', function () {
                prepareDatepickerWidget();
                keepDatepickerInDaysMode();
            });
            fromPicker.on('dp.update', function () {
                prepareDatepickerWidget();
                keepDatepickerInDaysMode();
            });
            toPicker.on('dp.update', function () {
                prepareDatepickerWidget();
                keepDatepickerInDaysMode();
            });
            dateRow.on('click', '.challenge-datetimepicker [data-action]', function () {
                setTimeout(function () {
                    prepareDatepickerWidget();
                    keepDatepickerInDaysMode();
                }, 0);
            });
            dateRow.on('mousedown touchstart click', '.challenge-datetimepicker th.picker-switch, .challenge-datetimepicker .picker-switch', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                keepDatepickerInDaysMode();
                return false;
            });

            fromPicker.on('dp.change', function (e) {
                syncSharedField(this);
                setTimeout(function () {
                    syncWinnerSelectionLock(form, e.date);
                }, 0);
            });
            toPicker.on('dp.change', function () {
                syncSharedField(this);
            });
        });

        $(document).on('input change', '.challenge-form :input', function () {
            if ($(this).attr('type') === 'file') {
                return;
            }

            syncSharedField(this);
            scheduleChallengeDraftSave();
        });

        function toggleChallengeCoin(form) {
            var coinBlock = form.find('.challenge-coin-block');
            var coinInput = coinBlock.find('input[name="amount"][data-coin]');
            var isOnce = form.find('input[name="rhythm_visual"]:checked').val() === 'once';

            if (coinInput.data('initialReadonly') === undefined) {
                coinInput.data('initialReadonly', coinInput.prop('readonly'));
            }

            coinBlock.toggleClass('is-inactive', isOnce);
            coinInput.prop('readonly', isOnce || coinInput.data('initialReadonly'));
        }

        function toggleCheckinByRhythm(form) {
            var checkinBlock = form.find('.challenge-checkin-block');
            if (checkinBlock.attr('data-time-locked') === '1') {
                return;
            }
            var isOnce = form.find('input[name="rhythm_visual"]:checked').val() === 'once';

            if (isOnce) {
                checkinBlock.find('input[name="checkin_visual"][value="story"]').prop('checked', true);
            }

            checkinBlock.toggleClass('is-inactive', isOnce);
        }

        function isSingleParticipantMode(form) {
            return form.find('input[name="participants_visual"]:checked').val() === '1';
        }

        function toggleRewardBlock(form) {
            var rewardBlock = form.find('.challenge-reward-block');
            var rewardInput = rewardBlock.find('input[name="reward_amount"]');
            var isSingle = isSingleParticipantMode(form);
            var timeLocked = rewardBlock.attr('data-time-locked') === '1';

            rewardBlock.toggleClass('is-inactive', isSingle || timeLocked);
            rewardInput.prop('readonly', isSingle || timeLocked);
        }

        function updateSubmitState(form) {
            form.find('button[type="submit"]').prop('disabled', false);
        }

        function showChallengeFormToast(message) {
            if (window.toastr && typeof window.toastr.error === 'function') {
                window.toastr.error(message, 'Ошибка', window.toastr_options || {closeButton: true});
                return;
            }

            $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> ' + message + '</div>');
            if (window.scheduleAlertAutoClose) {
                window.scheduleAlertAutoClose($('.alert-container'));
            }
        }

        function dateFieldMessage(form) {
            var fromInput = form.find('[name="date_from_visual"]');
            var toInput = form.find('[name="date_to_visual"]');
            var fromValue = $.trim(fromInput.val() || '');
            var toValue = $.trim(toInput.val() || '');
            var fromOriginalValue = $.trim(fromInput.attr('data-original-value') || '');
            var toOriginalValue = $.trim(toInput.attr('data-original-value') || '');
            var now = moment().startOf('minute');
            var fromDate = fromValue ? moment(fromValue, 'DD.MM.YY HH:mm', true) : null;
            var toDate = toValue ? moment(toValue, 'DD.MM.YY HH:mm', true) : null;
            var isEditing = form.find('[name="challenge_id"], [name="battle_id"]').length > 0;
            var shouldValidateFromPast = !isEditing || fromValue !== fromOriginalValue;
            var shouldValidateToPast = !isEditing || toValue !== toOriginalValue;

            form.find('[name="date_from_visual"], [name="date_to_visual"]').removeClass('is-invalid');

            if (fromValue && !fromDate.isValid()) {
                form.find('[name="date_from_visual"]').addClass('is-invalid');
                return 'Укажите дату начала в формате ДД.ММ.ГГ ЧЧ:ММ';
            }
            if (toValue && !toDate.isValid()) {
                form.find('[name="date_to_visual"]').addClass('is-invalid');
                return 'Укажите дату окончания в формате ДД.ММ.ГГ ЧЧ:ММ';
            }
            if (shouldValidateFromPast && fromDate && !fromDate.isAfter(now)) {
                form.find('[name="date_from_visual"]').addClass('is-invalid');
                return 'Дата и время начала должны быть позже текущего времени';
            }
            if (shouldValidateToPast && toDate && toDate.isBefore(now)) {
                form.find('[name="date_to_visual"]').addClass('is-invalid');
                return 'Дата и время окончания не могут быть в прошлом';
            }
            if (isEditing && shouldValidateToPast && toDate && toDate.isBefore(now.clone().add(12, 'hours'))) {
                form.find('[name="date_to_visual"]').addClass('is-invalid');
                return 'До окончания должно оставаться не менее 12 часов';
            }
            if (fromDate && toDate && toDate.isBefore(fromDate)) {
                form.find('[name="date_to_visual"]').addClass('is-invalid');
                return 'Дата и время окончания не могут быть раньше начала';
            }

            return null;
        }

        function requiredFieldMessage(form) {
            if (!$.trim(form.find('[name="title"]').val() || '')) {
                return 'Заполните обязательное поле: Название';
            }

            var descriptionInput = form.find('[name="description"]');
            var description = $.trim(descriptionInput.val() || '');
            descriptionInput.removeClass('is-invalid');
            if (!description) {
                return 'Заполните обязательное поле: Описание';
            }
            if (description.split(/\s+/).filter(Boolean).length > 650) {
                descriptionInput.addClass('is-invalid');
                return 'Описание должно содержать не более 650 слов';
            }

            var coverInput = form.find('[name="mainImg"]')[0];
            var hasCover = form.attr('data-has-cover') === '1';
            if (!hasCover && (!coverInput || !coverInput.files || !coverInput.files.length)) {
                return 'Заполните обязательное поле: Обложка';
            }

            var dateMessage = dateFieldMessage(form);
            if (dateMessage) {
                return dateMessage;
            }

            var amountInput = form.find('[name="amount"][data-coin]');
            if (amountInput.length && !form.find('.challenge-coin-block').hasClass('is-inactive') && !$.trim(amountInput.val() || '')) {
                return 'Заполните обязательное поле: Челлендж-коин';
            }

            if (!form.find('[name="participants_visual"]:checked').length) {
                return 'Выберите число участников';
            }

            if (form.find('[name="participants_visual"]:checked').val() === 'limit' && !$.trim(form.find('[name="min_participants_limit"]').val() || '')) {
                return 'Укажите лимит участников';
            }

            if (!form.find('[name="visibility"]:checked').length) {
                return 'Выберите видимость';
            }

            if (form.find('[name="rhythm_visual"]').length && !form.find('[name="rhythm_visual"]:checked').length) {
                return 'Выберите ритм';
            }

            if (!form.find('[name="checkin_visual"]:checked').length) {
                return 'Выберите чек-ин';
            }

            var winnerBlock = form.find('.challenge-winner-block');
            if (winnerBlock.length && !winnerBlock.hasClass('is-inactive') && winnerBlock.find('[name="winner_selection"]').length && !winnerBlock.find('[name="winner_selection"]:checked').length) {
                return 'Выберите способ определения победителя';
            }

            var callBlock = form.find('.challenge-call-block');
            if (callBlock.length && !callBlock.find('[name="called_user_id"]').length) {
                return 'Выберите пользователя в блоке Вызов';
            }

            return null;
        }

        function validateRewardAmount(form) {
            var rewardBlock = form.find('.challenge-reward-block');
            var rewardInput = rewardBlock.find('input[name="reward_amount"]');
            var balance = parseInt(rewardBlock.attr('data-deels-balance'), 10) || 0;
            var value = parseInt(rewardInput.val(), 10) || 0;
            var isInvalid = !rewardBlock.hasClass('is-inactive') && rewardInput.val() !== '' && value > balance;

            rewardInput.toggleClass('is-invalid', isInvalid);
            rewardBlock.toggleClass('has-error', isInvalid);
            updateSubmitState(form);
        }

        function toggleWinnerBlock(form) {
            var winnerBlock = form.find('.challenge-winner-block');
            if (winnerBlock.attr('data-winner-locked') === '1') {
                return;
            }
            var likesOption = winnerBlock.find('.challenge-winner-by-likes');
            var likesInput = likesOption.find('input[name="winner_selection"]');
            var creatorInput = winnerBlock.find('input[name="winner_selection"][value="creator"]');
            var isSingle = isSingleParticipantMode(form);
            var isStoryCheckin = form.find('input[name="checkin_visual"]:checked').val() === 'story';

            winnerBlock.toggleClass('d-none', isSingle);

            if (!isStoryCheckin && likesInput.is(':checked') && creatorInput.length) {
                creatorInput.prop('checked', true);
            }

            likesOption.toggleClass('d-none', !isStoryCheckin);

            if (!isSingle && !winnerBlock.find('input[name="winner_selection"]:checked:visible').length) {
                var firstVisible = winnerBlock.find('input[name="winner_selection"]:visible').first();
                if (firstVisible.length) {
                    firstVisible.prop('checked', true);
                }
            }
        }

        function syncWinnerSelectionLock(form, selectedDate) {
            var winnerBlock = form.find('.challenge-winner-block');
            if (!winnerBlock.length) {
                return;
            }

            var isEditing = form.attr('data-edit-mode') === '1';
            var hardStarted = form.attr('data-has-started') === '1';
            var startInput = form.find('input[name="date_from_visual"]');
            var startValue = $.trim(startInput.val() || startInput.attr('data-original-value') || '');
            var picker = startInput.data('DateTimePicker');
            var startsAt = moment.isMoment(selectedDate)
                ? selectedDate.clone()
                : (picker && moment.isMoment(picker.date())
                    ? picker.date().clone()
                    : moment(startValue, 'DD.MM.YY HH:mm', true));
            var locked = isEditing
                && (hardStarted || (startsAt.isValid() && !startsAt.isAfter(moment())));
            var radios = winnerBlock.find('input[type="radio"][name="winner_selection"]');
            var hidden = winnerBlock.find('.challenge-winner-hidden');
            var rhythmBlock = form.find('.challenge-rhythm-block');
            var rhythmRadios = rhythmBlock.find('input[type="radio"][name="rhythm_visual"]');
            var rhythmHidden = rhythmBlock.find('.challenge-rhythm-hidden');
            var checkinBlock = form.find('.challenge-checkin-block');
            var checkinRadios = checkinBlock.find('input[type="radio"][name="checkin_visual"]');
            var checkinHidden = checkinBlock.find('.challenge-checkin-hidden');
            var rewardBlock = form.find('.challenge-reward-block');

            if (locked) {
                hidden.val(radios.filter(':checked').val() || hidden.val()).prop('disabled', false);
                radios.prop('disabled', true);
                rhythmHidden.val(rhythmRadios.filter(':checked').val() || rhythmHidden.val()).prop('disabled', false);
                rhythmRadios.prop('disabled', true);
                checkinHidden.val(checkinRadios.filter(':checked').val() || checkinHidden.val()).prop('disabled', false);
                checkinRadios.prop('disabled', true);
            } else {
                hidden.prop('disabled', true);
                radios.prop('disabled', false);
                rhythmHidden.prop('disabled', true);
                rhythmRadios.prop('disabled', false);
                checkinHidden.prop('disabled', true);
                checkinRadios.prop('disabled', false);
            }

            winnerBlock
                .attr('data-winner-locked', locked ? '1' : '0')
                .toggleClass('is-inactive', locked);
            rhythmBlock.toggleClass('is-inactive', locked);
            checkinBlock
                .attr('data-time-locked', locked ? '1' : '0')
                .toggleClass('is-inactive', locked);
            rewardBlock.attr('data-time-locked', locked ? '1' : '0');

            if (!locked) {
                toggleWinnerBlock(form);
                toggleCheckinByRhythm(form);
            }
            toggleRewardBlock(form);
        }

        function syncChallengeExtraBlocks(form) {
            toggleRewardBlock(form);
            toggleWinnerBlock(form);
            validateRewardAmount(form);
            toggleInviteBlock(form);
        }

        function toggleInviteBlock(form) {
            var inviteBlock = form.find('.challenge-invite-block');

            inviteBlock.removeClass('is-inactive');
            inviteBlock.find('input[name="invite_user_ids[]"]').prop('disabled', false);
        }

        $('.challenge-form').each(function () {
            var form = $(this);
            toggleChallengeCoin(form);
            toggleCheckinByRhythm(form);
            syncChallengeExtraBlocks(form);
            syncWinnerSelectionLock(form);
        });

        $(document).on('change', 'input[name="rhythm_visual"]', function () {
            var form = $(this).closest('.challenge-form');
            toggleChallengeCoin(form);
            toggleCheckinByRhythm(form);
            syncChallengeExtraBlocks(form);
        });

        function syncMinParticipants(form) {
            var selected = form.find('input[name="participants_visual"]:checked').val();
            var value = '';

            if (selected === 'limit') {
                value = form.find('input[name="min_participants_limit"]').val();
            } else if (selected === '1' || selected === '2') {
                value = selected;
            } else if (selected === '0') {
                value = 0;
            }

            form.find('input[name="min_participants"]').val(value);
        }

        function validateParticipantsLimit(form) {
            syncMinParticipants(form);
            var limitRadio = form.find('input[name="participants_visual"][value="limit"]');
            var limitInput = form.find('input[name="min_participants_limit"]');
            var isInvalid = limitRadio.is(':checked') && limitInput.val() === '';

            limitInput.toggleClass('is-invalid', isInvalid);
            updateSubmitState(form);
        }

        function editParticipantsMessage(form) {
            if (form.attr('data-edit-mode') !== '1' || form.find('.challenge-invite-block').attr('data-mode') === 'battle') {
                return null;
            }

            var originalMode = form.attr('data-original-participants-mode') || '0';
            var originalLimit = parseInt(form.attr('data-original-participants-limit'), 10) || 0;
            var actualParticipants = parseInt(form.attr('data-actual-participants'), 10) || 1;
            var selected = form.find('input[name="participants_visual"]:checked').val();
            var limitValue = parseInt(form.find('input[name="min_participants_limit"]').val(), 10) || 0;
            var newCount = selected === 'limit' ? limitValue : parseInt(selected, 10) || 0;
            var newMode = newCount === 1 ? '1' : (newCount > 1 ? 'limit' : '0');

            if (form.attr('data-has-started') === '1' && (newMode !== originalMode || newCount !== originalLimit)) {
                return 'Число участников нельзя менять после начала челленджа';
            }

            if (newMode === originalMode && newCount === originalLimit) {
                return null;
            }

            if (originalMode === '1') {
                return null;
            }

            if (originalMode === '0') {
                if (actualParticipants <= 1) {
                    return null;
                }
                if (newMode !== 'limit' || newCount < actualParticipants) {
                    return 'Можно сменить только на лимит не меньше текущего числа участников';
                }
                return null;
            }

            if (actualParticipants <= 1) {
                return null;
            }
            if (newMode !== '0' && (newMode !== 'limit' || newCount < actualParticipants)) {
                return 'Лимит участников не может быть меньше текущего числа участников';
            }

            return null;
        }

        $('.challenge-form').each(function () {
            syncMinParticipants($(this));
            validateParticipantsLimit($(this));
        });

        $(document).on('change', 'input[name="participants_visual"]', function () {
            var form = $(this).closest('.challenge-form');
            if ($(this).val() !== 'limit') {
                form.find('input[name="min_participants_limit"]').val('');
            }
            validateParticipantsLimit(form);
            syncChallengeExtraBlocks(form);
        });

        $(document).on('input', 'input[name="min_participants_limit"]', function () {
            var form = $(this).closest('.challenge-form');

            form.find('input[name="participants_visual"][value="limit"]').prop('checked', true);
            this.value = this.value.replace(/\D/g, '').slice(0, 3);
            validateParticipantsLimit(form);
            syncChallengeExtraBlocks(form);
        });

        function normalizeParticipantsLimitInput(input) {
            if (!input || input.value === '') {
                return;
            }

            var value = parseInt(input.value, 10);
            if (value < 2) value = 2;
            if (value > 100) value = 100;
            input.value = value;
        }

        $(document).on('blur change', 'input[name="min_participants_limit"]', function () {
            var form = $(this).closest('.challenge-form');

            form.find('input[name="participants_visual"][value="limit"]').prop('checked', true);
            normalizeParticipantsLimitInput(this);

            validateParticipantsLimit(form);
            syncChallengeExtraBlocks(form);
        });

        $(document).on('change', 'input[name="checkin_visual"]', function () {
            syncChallengeExtraBlocks($(this).closest('.challenge-form'));
        });

        $(document).on('input change', 'input[name="date_from_visual"]', function () {
            syncWinnerSelectionLock($(this).closest('.challenge-form'));
        });

        $(document).on('input', 'input[name="reward_amount"]', function () {
            this.value = this.value.replace(/\D/g, '');
            validateRewardAmount($(this).closest('.challenge-form'));
        });

        $(document).on('input', 'textarea[name="description"]', function () {
            var words = $.trim(this.value).split(/\s+/).filter(Boolean);
            $(this).toggleClass('is-invalid', words.length > 650);
        });

        function inviteUserTitle(user) {
            return $('<div>').text(user.name || user.username || ('user_' + user.id)).html();
        }

        function inviteUserTemplate(user, selected, locked) {
            return '<button class="challenge-invite-user ' + (selected ? 'is-selected' : '') + ' ' + (locked ? 'is-locked' : '') + '" type="button" data-user-id="' + user.id + '">' +
                '<img src="' + user.avatar + '" alt="">' +
                '<span>' + (user.username || user.name || ('user_' + user.id)) + '</span>' +
            '</button>';
        }

        function inviteChipTemplate(user, locked) {
            var title = inviteUserTitle(user);

            return '<span class="challenge-invite-chip ' + (locked ? 'is-locked' : '') + '" data-user-id="' + user.id + '" data-toggle="tooltip" data-tooltip="' + title + '" tabindex="0">' +
                '<img src="' + user.avatar + '" alt="' + title + '">' +
                (locked ? '' : '<button type="button" class="challenge-invite-chip__remove" aria-label="Удалить">×</button>') +
                '<input type="hidden" name="invite_user_ids[]" value="' + user.id + '">' +
            '</span>';
        }

        function inviteMoreChipTemplate(count) {
            return '<span class="challenge-invite-more">Еще ' + count + '</span>';
        }

        function inviteSelectedLoaderTemplate() {
            return '<span class="challenge-invite-selected-loader"><i class="fa fa-spinner fa-spin"></i> Загрузка...</span>';
        }

        function callUserTemplate(user, selected) {
            return '<button class="challenge-call-user ' + (selected ? 'is-selected' : '') + '" type="button" data-user-id="' + user.id + '">' +
                '<img src="' + user.avatar + '" alt="">' +
                '<span>' + (user.username || user.name || ('user_' + user.id)) + '</span>' +
            '</button>';
        }

        function callChipTemplate(user) {
            var title = inviteUserTitle(user);

            return '<span class="challenge-invite-chip" data-user-id="' + user.id + '" data-toggle="tooltip" data-tooltip="' + title + '" tabindex="0">' +
                '<img src="' + user.avatar + '" alt="' + title + '">' +
                '<button type="button" class="challenge-call-chip__remove challenge-invite-chip__remove" aria-label="Удалить">×</button>' +
                '<input type="hidden" name="called_user_id" value="' + user.id + '">' +
            '</span>';
        }

        function initInviteTooltips(block) {
            block.off('.inviteTooltip')
                .on('mouseenter.inviteTooltip focusin.inviteTooltip', '.challenge-invite-chip[data-tooltip]', function () {
                    var chip = $(this);
                    var tooltip = $('.challenge-invite-floating-tooltip');

                    if (!tooltip.length) {
                        tooltip = $('<div class="challenge-invite-floating-tooltip" role="tooltip"></div>').appendTo('body');
                    }

                    tooltip.text(chip.attr('data-tooltip')).addClass('is-visible');

                    var chipRect = this.getBoundingClientRect();
                    var tooltipRect = tooltip[0].getBoundingClientRect();
                    var left = chipRect.left + (chipRect.width / 2) - (tooltipRect.width / 2);
                    var top = chipRect.top - tooltipRect.height - 8;

                    left = Math.max(8, Math.min(left, window.innerWidth - tooltipRect.width - 8));
                    if (top < 8) {
                        top = chipRect.bottom + 8;
                    }

                    tooltip.css({left: left + 'px', top: top + 'px'});
                })
                .on('mouseleave.inviteTooltip focusout.inviteTooltip', '.challenge-invite-chip[data-tooltip]', function () {
                    $('.challenge-invite-floating-tooltip').removeClass('is-visible');
                });
        }

        function getInviteState(block) {
            var state = block.data('inviteState');
            if (!state) {
                var locked = {};
                $.each(String(block.attr('data-original-selected-ids') || '').split(','), function (_, id) {
                    id = $.trim(id);
                    if (id) {
                        locked[id] = true;
                    }
                });
                state = {
                    loaded: false,
                    users: {},
                    friends: [],
                    random: [],
                    selected: {},
                    locked: locked,
                    other: {},
                    searchTimer: null
                };
                block.data('inviteState', state);
            }
            return state;
        }

        function getCallState(block) {
            var state = block.data('callState');
            if (!state) {
                state = {
                    loaded: false,
                    users: {},
                    friends: [],
                    random: [],
                    selected: null,
                    searchTimer: null
                };
                block.data('callState', state);
            }
            return state;
        }

        function selectedInviteIds(block) {
            var state = getInviteState(block);
            return Object.keys(state.selected);
        }

        function syncSharedInviteBlock(sourceBlock) {
            if (challengeCreateEditMode || sharedChallengeInviteSyncing) {
                return;
            }

            sharedChallengeInviteSyncing = true;

            var sourceState = getInviteState(sourceBlock);
            $('.challenge-invite-block').not(sourceBlock).each(function () {
                var targetBlock = $(this);
                var targetState = getInviteState(targetBlock);

                targetState.selected = $.extend(true, {}, sourceState.selected);
                targetState.other = {};
                $.each(targetState.selected, function (userId, user) {
                    targetState.users[userId] = user;
                    if (!user.is_friend) {
                        targetState.other[userId] = user;
                    }
                });
                targetBlock.attr('data-selected-ids', Object.keys(targetState.selected).join(','));
                renderOtherInviteList(targetBlock);
                syncInviteSelected(targetBlock);
            });

            sharedChallengeInviteSyncing = false;
        }

        function rememberInviteUsers(state, users) {
            $.each(users || [], function (_, user) {
                state.users[user.id] = user;
            });
        }

        function syncCallSelected(block) {
            var state = getCallState(block);
            var selected = block.find('.challenge-call-selected').empty();

            if (state.selected) {
                selected.append(callChipTemplate(state.selected));
            }

            block.find('.add_call').removeClass('d-none');
            block.find('.challenge-call-user').each(function () {
                var userId = String($(this).attr('data-user-id'));
                $(this).toggleClass('is-selected', !!state.selected && String(state.selected.id) === userId);
            });
            validateCallBlock(block.closest('.challenge-form'), false);
        }

        function validateCallBlock(form, showError) {
            var block = form.find('.challenge-call-block');
            if (!block.length) {
                updateSubmitState(form);
                return;
            }

            var state = getCallState(block);
            var isInvalid = !state.selected;

            block.toggleClass('is-invalid', !!showError && isInvalid);
            updateSubmitState(form);
        }

        function renderCallList(block, listName, users) {
            var state = getCallState(block);
            var list = block.find('[data-list="' + listName + '"]').empty();

            if (!users || !users.length) {
                list.append('<p class="challenge-invite-empty">Нет пользователей</p>');
                return;
            }

            $.each(users, function (_, user) {
                list.append(callUserTemplate(user, !!state.selected && String(state.selected.id) === String(user.id)));
            });
        }

        function renderInviteListLoader(block, listName) {
            block.find('[data-list="' + listName + '"]')
                .empty()
                .append('<p class="challenge-invite-empty"><i class="fa fa-spinner fa-spin"></i> Загрузка...</p>');
        }

        function syncInviteSelected(block) {
            var state = getInviteState(block);
            var selected = block.find('.challenge-invite-selected').empty();

            if (block.data('draftInviteLoading')) {
                selected.append(inviteSelectedLoaderTemplate());
                return;
            }

            $.each(state.selected, function (_, user) {
                selected.append(inviteChipTemplate(user, !!state.locked[String(user.id)]));
            });
            updateInviteSelectedOverflow(block);

            block.find('.challenge-invite-user').each(function () {
                var userId = String($(this).attr('data-user-id'));
                $(this).toggleClass('is-selected', !!state.selected[userId]);
                $(this).toggleClass('is-locked', !!state.locked[userId]);
            });
            initInviteTooltips(block);
            syncSharedInviteBlock(block);
        }

        function updateInviteSelectedOverflow(block) {
            var wrap = block.find('.challenge-invite-selected-wrap');
            var selected = block.find('.challenge-invite-selected');
            var chips = selected.find('.challenge-invite-chip');
            var maxWidth = wrap.length ? wrap.innerWidth() : selected.parent().innerWidth();

            if (wrap.length && wrap[0].getBoundingClientRect) {
                var rect = wrap[0].getBoundingClientRect();
                var viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
                var visibleWidth = viewportWidth ? viewportWidth - rect.left - 24 : maxWidth;
                maxWidth = Math.max(0, Math.min(maxWidth, visibleWidth));
            }

            selected.find('.challenge-invite-more').remove();
            chips.removeClass('is-overflow-hidden');

            if (!chips.length || !maxWidth) {
                return;
            }

            var more = $(inviteMoreChipTemplate(chips.length)).appendTo(selected);
            var moreWidth = more.outerWidth(true) || 64;
            var used = 0;
            var hiddenCount = 0;

            chips.each(function () {
                var chip = $(this);
                var chipWidth = chip.outerWidth(true) + 8;

                if (used + chipWidth + moreWidth > maxWidth) {
                    chip.addClass('is-overflow-hidden');
                    hiddenCount++;
                    return;
                }

                used += chipWidth;
            });

            if (hiddenCount > 0) {
                more.text('Еще ' + hiddenCount);
            } else {
                more.remove();
            }
        }

        function updateAllInviteSelectedOverflow() {
            $('.challenge-invite-block').each(function () {
                updateInviteSelectedOverflow($(this));
            });
        }

        function scheduleInviteSelectedOverflowUpdate() {
            updateAllInviteSelectedOverflow();
            window.requestAnimationFrame(function () {
                updateAllInviteSelectedOverflow();
            });
        }

        function renderInviteList(block, listName, users) {
            var state = getInviteState(block);
            var list = block.find('[data-list="' + listName + '"]').empty();
            var visibleUsers = users || [];

            if (listName === 'random') {
                visibleUsers = $.grep(visibleUsers, function (user) {
                    return !state.other[String(user.id)];
                });
            }

            if (!visibleUsers.length) {
                list.append('<p class="challenge-invite-empty">Нет пользователей</p>');
                return;
            }

            $.each(visibleUsers, function (_, user) {
                var userId = String(user.id);
                list.append(inviteUserTemplate(user, !!state.selected[userId], !!state.locked[userId]));
            });
            initInviteTooltips(block);
        }

        function prepareOtherInviteList(block) {
            var state = getInviteState(block);
            state.other = {};

            $.each(state.selected, function (userId, user) {
                if (!user.is_friend) {
                    state.other[userId] = user;
                }
            });

            renderOtherInviteList(block);
            renderInviteList(block, 'random', state.random);
        }

        function renderOtherInviteList(block) {
            var state = getInviteState(block);
            var otherUsers = [];

            $.each(state.other, function (_, user) {
                if (!user.is_friend) {
                    otherUsers.push(user);
                }
            });

            block.find('[data-section="other"]').toggleClass('d-none', otherUsers.length === 0);
            renderInviteList(block, 'other', otherUsers);
        }

        function resetInviteSearch(block) {
            block.find('.challenge-invite-search').val('');
            block.find('[data-section="results"]').addClass('d-none');
            block.find('[data-section="friends"]').removeClass('is-collapsed');
            block.find('[data-section="random"]').removeClass('d-none is-collapsed');
            block.find('[data-section="other"]').removeClass('is-collapsed');
        }

        function closeInviteBlock(block) {
            block.removeClass('is-open');
            resetInviteSearch(block);
        }

        function closeCallBlock(block) {
            var state = getCallState(block);

            block.removeClass('is-open');
            block.find('.challenge-call-search').val('');
            block.find('[data-section="call-results"]').addClass('d-none');
            block.find('[data-section="call-friends"]').removeClass('is-collapsed');
            block.find('[data-section="call-random-users"]').removeClass('d-none is-collapsed');
            renderCallList(block, 'call-friends', state.friends);
            renderCallList(block, 'call-random-users', state.random);
            syncCallSelected(block);
        }

        function loadInviteUsers(block, query) {
            var state = getInviteState(block);
            var ids = selectedInviteIds(block).join(',') || block.attr('data-selected-ids') || '';

            if (!query) {
                block.find('[data-section="random"]').removeClass('d-none is-collapsed');
                renderInviteListLoader(block, 'random');
            }

            return $.ajax({
                type: 'GET',
                url: block.attr('data-invite-url'),
                data: {
                    q: query || '',
                    ids: ids
                },
                success: function (response) {
                    if (!response || !response.success) {
                        return;
                    }

                    rememberInviteUsers(state, response.friends);
                    rememberInviteUsers(state, response.random);
                    rememberInviteUsers(state, response.results);
                    rememberInviteUsers(state, response.selected);

                    $.each(response.selected || [], function (_, user) {
                        state.selected[String(user.id)] = user;
                    });

                    if (query) {
                        block.find('[data-section="friends"]').addClass('is-collapsed');
                        block.find('[data-section="random"]').addClass('d-none');
                        block.find('[data-section="other"]').addClass('is-collapsed');
                        block.find('[data-section="results"]').removeClass('d-none');
                        renderInviteList(block, 'results', response.results || []);
                    } else {
                        state.friends = response.friends || [];
                        state.random = response.random || [];
                        renderInviteList(block, 'friends', state.friends);
                        renderInviteList(block, 'random', state.random);
                        block.find('[data-section="results"]').addClass('d-none');
                        block.find('[data-section="friends"]').removeClass('is-collapsed');
                        block.find('[data-section="random"]').removeClass('d-none is-collapsed');
                        block.find('[data-section="other"]').removeClass('is-collapsed');
                        prepareOtherInviteList(block);
                    }
                    syncInviteSelected(block);
                    state.loaded = true;
                }
            }).always(function () {
                if (block.data('draftInviteLoading')) {
                    block.removeData('draftInviteLoading');
                    syncInviteSelected(block);
                }
            });
        }

        function loadCallUsers(block, query) {
            var state = getCallState(block);
            var ids = state.selected ? state.selected.id : (block.attr('data-selected-id') || '');

            if (!query) {
                block.find('[data-section="call-random-users"]').removeClass('d-none is-collapsed');
                renderInviteListLoader(block, 'call-random-users');
            }

            return $.ajax({
                type: 'GET',
                url: block.attr('data-invite-url'),
                data: {
                    q: query || '',
                    ids: ids,
                    all_random: 1
                },
                success: function (response) {
                    if (!response || !response.success) {
                        return;
                    }

                    rememberInviteUsers(state, response.friends);
                    rememberInviteUsers(state, response.random);
                    rememberInviteUsers(state, response.results);
                    rememberInviteUsers(state, response.selected);

                    if (response.selected && response.selected.length) {
                        state.selected = response.selected[0];
                    }

                    if (query) {
                        block.find('[data-section="call-friends"]').addClass('is-collapsed');
                        block.find('[data-section="call-random-users"]').addClass('d-none');
                        block.find('[data-section="call-results"]').removeClass('d-none');
                        renderCallList(block, 'call-results', response.results || []);
                    } else {
                        state.friends = response.friends || [];
                        state.random = response.random || [];
                        block.find('[data-section="call-results"]').addClass('d-none');
                        block.find('[data-section="call-friends"]').removeClass('is-collapsed');
                        block.find('[data-section="call-random-users"]').removeClass('d-none is-collapsed');
                        renderCallList(block, 'call-friends', state.friends);
                        renderCallList(block, 'call-random-users', state.random);
                    }
                    syncCallSelected(block);
                    state.loaded = true;
                }
            });
        }

        restoreChallengeDraft();

        $('.challenge-invite-block').each(function () {
            var block = $(this);
            var state = getInviteState(block);
            var draftInviteUsers = block.data('draftInviteUsers') || {};
            var selectedIds = (block.attr('data-selected-ids') || '').split(',').filter(Boolean);

            $.each(selectedIds, function (_, id) {
                var draftUser = draftInviteUsers[String(id)] || draftInviteUsers[id] || null;
                state.selected[String(id)] = draftUser || {
                    id: id,
                    username: '',
                    name: '',
                    avatar: '/default_avatars/avatar_1.png',
                    is_friend: false
                };
            });

            if (selectedIds.length) {
                if (Object.keys(draftInviteUsers).length) {
                    block.data('draftInviteLoading', true);
                    syncInviteSelected(block);
                }
                loadInviteUsers(block);
            } else {
                syncInviteSelected(block);
            }
        });

        $('.challenge-call-block').each(function () {
            var block = $(this);
            var state = getCallState(block);
            var draftCallUser = block.data('draftCallUser') || null;
            var selectedId = block.attr('data-selected-id');

            if (selectedId) {
                state.selected = draftCallUser || {
                    id: selectedId,
                    username: '',
                    name: '',
                    avatar: '/default_avatars/avatar_1.png'
                };
                loadCallUsers(block);
            } else {
                syncCallSelected(block);
            }
            validateCallBlock(block.closest('.challenge-form'), false);
        });

        $(document).on('click', '.add_invite', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var block = $(this).closest('.challenge-invite-block');
            if (block.hasClass('is-inactive')) {
                return;
            }

            $('.challenge-invite-block').not(block).each(function () {
                closeInviteBlock($(this));
            });
            $('.challenge-call-block').each(function () {
                closeCallBlock($(this));
            });
            block.toggleClass('is-open');

            if (block.hasClass('is-open')) {
                resetInviteSearch(block);
                if (!getInviteState(block).loaded) {
                    loadInviteUsers(block);
                } else {
                    prepareOtherInviteList(block);
                }
            }
        });

        $(document).on('click', '.add_call', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var block = $(this).closest('.challenge-call-block');

            $('.challenge-call-block').not(block).each(function () {
                closeCallBlock($(this));
            });
            $('.challenge-invite-block').each(function () {
                closeInviteBlock($(this));
            });
            block.toggleClass('is-open');

            if (block.hasClass('is-open') && !getCallState(block).loaded) {
                loadCallUsers(block);
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.challenge-invite-block').length) {
                $('.challenge-invite-block').each(function () {
                    closeInviteBlock($(this));
                });
            }
            if (!$(e.target).closest('.challenge-call-block').length) {
                $('.challenge-call-block').each(function () {
                    closeCallBlock($(this));
                });
            }
        });

        $(document).on('click', '.challenge-invite-block.is-open', function (e) {
            if ($(e.target).closest('.challenge-invite-dropdown, .add_invite, .challenge-invite-selected').length) {
                return;
            }

            closeInviteBlock($(this));
        });

        $(document).on('click', '.challenge-call-block.is-open', function (e) {
            if ($(e.target).closest('.challenge-call-dropdown, .add_call, .challenge-call-selected').length) {
                return;
            }

            closeCallBlock($(this));
        });

        $(document).on('click', '.challenge-invite-section__title', function () {
            var section = $(this).closest('.challenge-invite-section');
            if ($.inArray(section.attr('data-section'), ['results', 'call-results']) === -1) {
                section.toggleClass('is-collapsed');
            }
        });

        $(document).on('input', '.challenge-invite-search', function () {
            var input = $(this);
            var block = input.closest('.challenge-invite-block');
            var state = getInviteState(block);
            var query = input.val().trim();

            clearTimeout(state.searchTimer);
            state.searchTimer = setTimeout(function () {
                if (query) {
                    loadInviteUsers(block, query);
                } else {
                    resetInviteSearch(block);
                    loadInviteUsers(block);
                }
            }, 300);
        });

        $(window).on('resize', function () {
            updateAllInviteSelectedOverflow();
        });

        $(document).on('input', '.challenge-call-search', function () {
            var input = $(this);
            var block = input.closest('.challenge-call-block');
            var state = getCallState(block);
            var query = input.val().trim();

            clearTimeout(state.searchTimer);
            state.searchTimer = setTimeout(function () {
                loadCallUsers(block, query);
            }, 300);
        });

        $(document).on('click', '.challenge-invite-user', function () {
            var block = $(this).closest('.challenge-invite-block');
            var state = getInviteState(block);
            var userId = String($(this).attr('data-user-id'));
            var user = state.users[userId];
            var listName = $(this).closest('.challenge-invite-list').attr('data-list');

            if (!user) {
                return;
            }
            if (state.locked[userId]) {
                return;
            }

            if (listName === 'friends') {
                user.is_friend = true;
            }

            if (state.selected[userId]) {
                delete state.selected[userId];
            } else {
                state.selected[userId] = user;
            }

            syncInviteSelected(block);
            scheduleChallengeDraftSave();
        });

        $(document).on('click', '.challenge-call-user', function () {
            var block = $(this).closest('.challenge-call-block');
            var state = getCallState(block);
            var userId = String($(this).attr('data-user-id'));
            var user = state.users[userId];

            if (!user) {
                return;
            }

            state.selected = user;
            syncCallSelected(block);
            closeCallBlock(block);
            scheduleChallengeDraftSave();
        });

        $(document).on('click', '.challenge-call-random', function (e) {
            e.preventDefault();

            var block = $(this).closest('.challenge-call-block');
            var state = getCallState(block);

            function pickRandomUser() {
                if (!state.random.length) {
                    return;
                }

                state.selected = state.random[Math.floor(Math.random() * state.random.length)];
                syncCallSelected(block);
                closeCallBlock(block);
                scheduleChallengeDraftSave();
            }

            if (state.random.length) {
                pickRandomUser();
            } else {
                loadCallUsers(block).done(pickRandomUser);
            }
        });

        $(document).on('click', '.challenge-invite-chip__remove', function () {
            if ($(this).hasClass('challenge-call-chip__remove')) {
                return;
            }

            var block = $(this).closest('.challenge-invite-block');
            var state = getInviteState(block);
            var userId = String($(this).closest('.challenge-invite-chip').attr('data-user-id'));

            if (state.locked[userId]) {
                return;
            }

            delete state.selected[userId];
            renderOtherInviteList(block);
            syncInviteSelected(block);
            scheduleChallengeDraftSave();
        });

        $(document).on('click', '.challenge-call-chip__remove', function () {
            var block = $(this).closest('.challenge-call-block');
            var state = getCallState(block);

            state.selected = null;
            syncCallSelected(block);
            scheduleChallengeDraftSave();
        });

        $(document).on('submit', '.challenge-form', function (e) {
            var form = $(this);
            var message = requiredFieldMessage(form);
            var limitInput = form.find('input[name="min_participants_limit"]')[0];

            if (form.find('input[name="participants_visual"][value="limit"]').is(':checked')) {
                normalizeParticipantsLimitInput(limitInput);
            }
            syncMinParticipants(form);
            validateParticipantsLimit(form);
            validateRewardAmount(form);
            validateCallBlock(form, true);

            if (!message && form.find('.is-invalid').length) {
                if (form.find('input[name="min_participants_limit"].is-invalid').length) {
                    message = 'Укажите корректный лимит участников';
                } else if (form.find('input[name="reward_amount"].is-invalid').length) {
                    message = form.find('.challenge-reward-error').text() || 'Укажите корректную награду';
                } else if (form.find('.challenge-call-block.is-invalid').length) {
                    message = 'Выберите пользователя в блоке Вызов';
                } else {
                    message = 'Заполните обязательные поля';
                }
            }

            if (!message) {
                message = editParticipantsMessage(form);
            }

            if (message) {
                e.preventDefault();
                showChallengeFormToast(message);
            } else if (!challengeCreateEditMode) {
                try {
                    sessionStorage.setItem(challengeDraftSubmitKey, '1');
                } catch (submitDraftError) {}
            }
        });

        $(document).on('input', 'input[name="amount"][data-coin]', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 5);
        });

        $(document).on('blur change', 'input[name="amount"][data-coin]', function () {
            if (this.value === '') {
                return;
            }
            var value = parseInt(this.value, 10);
            if (value < 100) value = 100;
            if (value > 10000) value = 10000;
            this.value = value;
        });

        function readUrl(input, container) {
            if (input.files.length > 3) {
                alert('Вы можете выбрать не более 3х изображений');
                return false;
            }
            if (input.files && input.files[0]) {
                var filesAmount = input.files.length;

                for (let i = 0; i < filesAmount; i++) {
                    let reader = new FileReader();
                    reader.onload = (e) => {
                        var match = e.target.result.match(/^data:([^/]+)\/([^;]+);/) || [];
                        var type = match[1];
                        let html = '<img src="' + e.target.result + '">';
                        if(type == 'video') {
                            var video_data = e.target.result;
                            video_data = video_data.replace("/quicktime", "/mp4");
                            html = '<video controls class="video" src="'+video_data+'#t=0.001" style="width: 100%; max-width:203px;max-height:360px" type="video/mp4" loop playsinline>Ваш браузер не поддерживает HTML5 видео.</video>';
                        }
                        $(container).html(html);
                    }

                    reader.readAsDataURL(input.files[i]);
                }
            }
        }
    </script>
@endsection

@push('after_scripts')
    <link href="/plugins/video.js/dist/video-js.min.css" rel="stylesheet">
    <link href="/plugins/videojs.record.css" rel="stylesheet">
    <link href="/plugins/examples.css" rel="stylesheet">
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script src="/plugins/recordrtc/RecordRTC.js"></script>
    <script src="/plugins/webrtc-adapter/out/adapter.js"></script>
    <script src="/plugins/videojs.record.js?v=1"></script>
    <script src="/plugins/browser-workarounds.js"></script>
@endpush
