<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Utility\Hash;

class GoogleRecaptchaComponent extends Component {

    public function verify($response, $request) {
        $session = $request->getSession();
    
        // Verificar se já validou o recaptcha antes
        if ($session->check('recaptcha_valid') && $session->read('recaptcha_valid') === true) {
            return true;
        }
    
        $client = new Client();
        $remoteIp = $request->clientIp();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => Configure::read('GoogleRecaptcha.secretKey'),
            'response' => $response,
            'remoteip' => $remoteIp
        ]);
        $responseBody = Hash::get((array)json_decode($response->getBody()), 'success');
    
        if (!$responseBody) {
            return false;
        } else {
            // Armazenar o resultado da validação em uma variável de sessão
            $session->write('recaptcha_valid', true);
            return true;
        }
    }
    
}
