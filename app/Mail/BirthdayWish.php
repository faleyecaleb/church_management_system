<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BirthdayWish extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The member instance.
     *
     * @var \App\Models\Member
     */
    public $member;

    /**
     * The member's age.
     *
     * @var int
     */
    public $age;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Member  $member
     * @param  int  $age
     * @return void
     */
    public function __construct(Member $member, $age)
    {
        $this->member = $member;
        $this->age = $age;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Happy Birthday!',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.birthday-wish',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}