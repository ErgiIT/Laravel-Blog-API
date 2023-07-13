<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PostShared extends Mailable
{
    protected $share;

    public function __construct($share)
    {
        $this->share = $share;
    }

    public function build()
    {
        return $this->view('emails.post_shared')
            ->with(['share' => $this->share])
            ->subject('A post has been shared with you');
    }
}
