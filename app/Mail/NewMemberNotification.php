<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Group;
use App\Models\User;

class NewMemberNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $user;

    public function __construct(Group $group, User $user)
    {
        $this->group = $group;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('New Member Joined Your Group')
            ->view('mail.new_member_notification');
    }
}
