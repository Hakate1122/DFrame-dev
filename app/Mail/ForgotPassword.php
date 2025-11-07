<?php
namespace App\Mail;

use DFrame\Application\Mail;

/**
 * Mail class for handling password reset emails.
 */
class ForgotPassword extends Mail{
    public function build(){
        return $this->subject('Quên mật khẩu')->view('email.forget_password');
    }
}