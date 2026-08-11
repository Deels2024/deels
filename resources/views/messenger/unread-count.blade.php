<?php $count = \Cmgmyr\Messenger\Models\Thread::forUserWithNewMessages(Auth::id())->latest('updated_at')->count(); ?>
@if($count > 0)
    <span class="label label-danger">{{ $count }}</span>
@endif
