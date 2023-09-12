<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Http\Client;
use Cake\Core\Configure;

class SolicitacaoController extends AppController{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

     public function initialize(): void{

        parent::initialize();

        //$this->loadComponent('GoogleRecaptcha');
        Configure::load('config_servico');
        $this->loadComponent('GoogleRecaptcha');
    }


    public function index(){   
        
        $refererUrl = $this->referer();
        $this->set(compact('refererUrl'));

    }


    public function verificaContribuinte() {

        // Dados do formulário
        $parametros = $this->request->getQuery(); // Obtém todos os parâmetros enviados na requisição
        $session = $this->getRequest()->getSession();
        $tipo_contribuinte = $parametros['campo']; 

        // Dados da API
        $config_servico = Configure::read('servico');

        if($tipo_contribuinte == '2'){ //PESSOA JURIDICA

            $apiUrl = $config_servico['api_verifica_cnpj_rf']['url'];

            if ($this->request->is('get')) {

                // Verificar se já validou o recaptcha antes
                if($session->check('recaptcha_valid') && $session->read('recaptcha_valid') === true) {

                    $opcoes = [
                        'timeout' => 60,
                    ];

                    $client = new Client($opcoes);

                    // Enviar a requisição para a API com os parâmetros na URL 
                    $response = $client->get($apiUrl, $parametros);

                    $resultado = json_decode($response->getBody()->getContents(), true);   
    
                    $this->set(compact('resultado','parametros'));
                    $this->viewBuilder()->setOption('serialize', ['resultado']);
                    
                }else if($this->GoogleRecaptcha->verify($this->request->getQuery('g-recaptcha-response'),$this->request) == true){

                    $session->write('recaptcha_valid', true);

                    $opcoes = [
                        'timeout' => 60,
                    ];
                    
                    $client = new Client($opcoes);

                    // Enviar a requisição para a API com os parâmetros na URL 
                    $response = $client->get($apiUrl, $parametros);
                    
                    $resultado = json_decode($response->getBody()->getContents(), true); 
        
                    $this->set(compact('resultado','parametros'));
                    $this->viewBuilder()->setOption('serialize', ['resultado']);

                }else {
                    $this->Flash->error(__('Por favor, remarque o captcha para validar a consulta.<i class="material-icons icon-close">close</i>'), ['escape' => false]);
                    $this->redirect($this->referer());
                }
            }


        }else if($tipo_contribuinte == '1'){ //PESSOA FISICA

            $apiUrl = $config_servico['api_verifica_pessoa_rf']['url'];

            if ($this->request->is('get')) {

                // Verificar se já validou o recaptcha antes
                if($session->check('recaptcha_valid') && $session->read('recaptcha_valid') === true) {

                    $client = new Client();

                    // Enviar a requisição para a API com os parâmetros na URL 
                    $response = $client->get($apiUrl, $parametros);

                    $resultado = json_decode($response->getBody()->getContents(), true);   
    
                    $this->set(compact('resultado','parametros'));
                    $this->viewBuilder()->setOption('serialize', ['resultado']);
                    
                }else if($this->GoogleRecaptcha->verify($this->request->getQuery('g-recaptcha-response'),$this->request) == true){

                    $session->write('recaptcha_valid', true);

                    $client = new Client();

                    // Enviar a requisição para a API com os parâmetros na URL 
                    $response = $client->get($apiUrl, $parametros);
                    
                    $resultado = json_decode($response->getBody()->getContents(), true); 
        
                    $this->set(compact('resultado','parametros'));
                    $this->viewBuilder()->setOption('serialize', ['resultado']);

                }else {
                    $this->Flash->error(__('Por favor, remarque o captcha para validar a consulta.<i class="material-icons icon-close">close</i>'), ['escape' => false]);
                    $this->redirect($this->referer());
                }
            }

        }

        

    }

    public function solicitaCertidao(){

        // Dados do formulário
        $parametros = $this->request->getQuery(); // Obtém todos os parâmetros enviados na requisição

        // Dados da API
        $config_servico = Configure::read('servico');
        $apiUrl = $config_servico['api_solicita_certidao']['url'];

        if ($this->request->is('get')) {
            
            $client = new Client();

            // Enviar a requisição para a API com os parâmetros na URL 
            $response = $client->get($apiUrl, $parametros);
                
            $resultado = $response->getBody()->getContents();

            $arrayResultado = json_decode($resultado, true);
    
            $this->set(compact('arrayResultado','parametros'));
            $this->viewBuilder()->setOption('serialize', ['arrayResultado','parametros']);

        }

    }




    public function imprimirCertidao($id_solicitacao) {

        // Dados da API
        $client = new Client();
        $config_servico = Configure::read('servico');
        $apiUrl = $config_servico['api_imprime_certidao']['url'];

        $response = $client->get($apiUrl, 
            ['id_solicitacao' => $id_solicitacao]
        );

        // Configurar a resposta do controlador
        $this->response = $this->response
            ->withType('application/pdf')
            ->withStringBody((string)$response->getBody());

        return $this->response;

    }

}




