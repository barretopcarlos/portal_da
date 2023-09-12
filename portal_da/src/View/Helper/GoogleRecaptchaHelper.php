<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\Core\Configure;

class GoogleRecaptchaHelper extends Helper {
    
    public function display() {
        $publicKey = Configure::read('GoogleRecaptcha.publicKey');
        return '<div class="g-recaptcha" data-sitekey="'.$publicKey.'"></div>';
    }

}
