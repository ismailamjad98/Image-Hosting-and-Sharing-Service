<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $url;
    public $reset_password;
    public $email;

    public function __construct($url, $email, $reset_password)
    {
        //
        $this->url = $url;
        $this->email = $email;
        $this->reset_password = $reset_password;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->from('ismail@gmail.com', 'Image Hosting')
            ->subject('Password Reset')
            ->view('forget_password');
    }
}
