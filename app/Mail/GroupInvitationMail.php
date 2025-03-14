<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Group;

class GroupInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $url;
    public $isExistingUser;

    public function __construct(Group $group, $url, $isExistingUser)
    {
        $this->group = $group;
        $this->url = $url;
        $this->isExistingUser = $isExistingUser;
    }

    public function build()
    {
        return $this->subject("You're invited to join {$this->group->name} on PiggyBang")
            ->view('mail.group_invitation');
    }
}
