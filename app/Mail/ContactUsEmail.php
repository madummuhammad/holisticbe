<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = request('name');
        $email = request('email');
        $subject = request('subject');
        $description = request('description');
        return $this->from('no-reply@holisticstations.com')
        ->subject('Pesan Dari ' . $subject)
        ->view('contact-us')
        ->with(
            [
                'name' => $name,
                'email' => $email,
                'description' => $description,
            ]);

    }
}
