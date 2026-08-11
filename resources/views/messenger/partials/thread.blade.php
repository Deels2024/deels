<div class="chat-item" {!! $thread->userUnreadMessagesCount(Auth::id()) ? 'data-count="'.$thread->userUnreadMessagesCount(Auth::id()).'"'  : ''!!} data-thread="{{ $thread->id }}">
    <div class="chat-item__avatar" style="background-image: url({{ $thread->participantsAvatar(Auth::id()) }});"></div>
    <div class="chat-item__content">
        <div class="chat-item__name">{{ $thread->participantsString(Auth::id()) }}</div>
        @php
            $content = preg_replace('#<a.*?>(.*?)</a>#i', '', $thread->latestMessage->body ?? '')
        @endphp
        <div class="chat-item__message">{{ strip_tags($thread->latestMessage->body ?? '') }}</div>
    </div>
</div>