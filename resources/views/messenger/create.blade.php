<div class="chat-head chat-head--bd">
    <div class="chat-head__wrap">
        <div class="chat-item">
            <div class="chat-item__avatar" style="background-image: url('{{ $user->avatar }}');"></div>
            <div class="chat-item__content">
                <div class="chat-item__name">{{ $user->fullname }}</div>
            </div>
        </div>
        <button class="chat-head__btn close_chat" type="button" aria-label="close button" data-back-btn>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g opacity="0.6"><path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g></svg>
        </button>
    </div>
</div>

<div class="chat-body">
    <div class="chat-wrap"></div>
</div>

<div class="chat-footer">
    <form class="chat-footer__form send_message" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="user_id" value="{{Auth::id()}}">
        <input type="hidden" name="recipients[]" value="{{ $user->id }}">
        <div class="chat-footer__form-wrap">
            @if(Auth::user()->blockedBy($user->id) || $user->id == 0)
                <span class="text-light d-block text-center w-100">Вы не можете писать этому пользователю</span>
            @else
                <textarea name="message" id="" rows="1" placeholder="Ваше сообщение" required data-at-expandable>{{ old('message') }}</textarea>
            @endif
        </div>
        <button type="submit">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.91858 5.82293C2.52572 4.3515 4.03715 3.09435 5.41287 3.75007L36.1229 18.3915C37.48 19.0372 37.48 20.9686 36.1229 21.6144L5.41429 36.2572C4.03858 36.9115 2.52715 35.6544 2.92001 34.1829L6.71429 20.0029L2.91858 5.82293ZM8.64429 21.0744L5.19001 33.9886L34.5257 20.0029L5.19001 6.01721L8.64429 18.9315H24.6429C24.927 18.9315 25.1995 19.0444 25.4005 19.2453C25.6014 19.4462 25.7143 19.7188 25.7143 20.0029C25.7143 20.2871 25.6014 20.5596 25.4005 20.7605C25.1995 20.9615 24.927 21.0744 24.6429 21.0744H8.64429Z" fill="#00F0FF"/>
            </svg>
        </button>
    </form>
</div>


