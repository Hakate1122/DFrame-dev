<?php
namespace App\Mail; 

use Core\Application\Mail;

class Ads extends Mail{
    public function build(array $data = []){
        return $this->subject('Quảng cáo')->view('mail.ads', $data);
    }
}