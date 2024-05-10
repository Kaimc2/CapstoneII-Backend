<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use mysql_xdevapi\Schema;

class MyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    /**
     * Create a new message instance.
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset',
            to: [$this->email]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.my_mail',
            with: [
                'token' => $this->token,
                'email' => $this->email
            ]
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
//namespace App\Mail;
//
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Mail\Mailable;
//use Illuminate\Queue\SerializesModels;
//
//;
//
//class MyMail extends Mailable
//{
//    use Queueable, SerializesModels;
//
//    public $token;
//    public $email;
//
//    /**
//     * Create a new message instance.
//     *
//     * @return void
//     */
//    public function __construct($token, $email)
//    {
//        $this->token = $token;
//        $this->email = $email;
//    }
//
//    /**
//     * Build the message.
//     *
//     * @return $this
//     */
//    public function build()
//    {
//        return $this->from('hulmarady2@gmail.com')
//            ->to($this->email)
//            ->subject('Password Changing',)
//            ->with([
//                'token' => $this->token,
//                'email' => $this->email
//            ])
//            ->view('emails.my_mail');
//    }
//}

