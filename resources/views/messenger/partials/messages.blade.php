@foreach($messages_dates as $message_date => $messages)
    <div class="chat-wrap" data-info="{{$message_date}}">
        @foreach($messages as $message)
            @include('messenger.partials.message', ['message' => $message])
        @endforeach
    </div>
@endforeach
