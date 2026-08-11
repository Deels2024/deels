<!-- chat-head -->
<div class="chat-head chat-head--bd">
    <div class="chat-head__wrap">
        <div class="chat-item">
            <div class="chat-item__avatar" style="background-image: url('{{ $thread->participantsAvatar(Auth::id()) }}');"></div>
            <div class="chat-item__content">
                <div class="chat-item__name">{{ $thread->participantsString(Auth::id()) }}</div>
            </div>
        </div>
        <button class="chat-head__btn close_chat" type="button" aria-label="close button" data-back-btn>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g opacity="0.6"><path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g></svg>
        </button>
    </div>
</div>
<!-- chat-body -->
<div class="chat-body messages_body">
    @include('messenger.partials.messages', ['messages_dates' => $messages_dates])
</div>
<div class="chat-message chat-message assistant_loader" style="display: none">
    <div class="chat-item">
        <div class="chat-item__avatar chat-item__avatar--xs" style="background-image: url('/default_avatars/robot.jpeg');"></div>
        <p>
            <span class="dotsContainer">
                <span id="dot1"></span>
                <span id="dot2"></span>
                <span id="dot3"></span>
            </span>
        </p>
    </div>
</div>
<!-- chat-footer -->
<div class="chat-footer">
    @include('messenger.partials.form-message')
</div>
