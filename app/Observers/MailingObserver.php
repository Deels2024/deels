<?php

namespace App\Observers;

use App\Models\Mailing;

class MailingObserver
{
    /**
     * Handle the Story "created" event.
     *
     * @param  \App\Models\Mailing  $mailing
     * @return void
     */
    public function created(Mailing $mailing)
    {
       
    }

    /**
     * Handle the Story "updated" event.
     *
     * @param  \App\Models\Story  $story
     * @return void
     */
    public function updated(Mailing $mailing)
    {

    }

    public function updating(Mailing $mailing)
    {
        if($mailing->sent_count == $mailing->receivers_count && $mailing->receivers_count > 0) {
            $mailing->status = 'done';
        }
    }



    /**
     * Handle the Story "deleted" event.
     *
     * @param  \App\Models\Story  $story
     * @return void
     */
    public function deleted(Mailing $mailing)
    {
        //
    }

    /**
     * Handle the Story "restored" event.
     *
     * @param  \App\Models\Story  $story
     * @return void
     */
    public function restored(Mailing $mailing)
    {
        //
    }

    /**
     * Handle the Story "force deleted" event.
     *
     * @param  \App\Models\Story  $story
     * @return void
     */
    public function forceDeleted(Mailing $mailing)
    {
        //
    }
}
