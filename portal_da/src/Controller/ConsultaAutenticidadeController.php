<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Core\Configure;
use Cake\Http\Client;

class ConsultaAutenticidadeController extends AppController{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

     public function initialize(): void{
        parent::initialize();

        Configure::load('config_servico');
        $this->loadComponent('GoogleRecaptcha');
    }


    public function index(){   
        
        $sucesso = $this->request->getSession()->read('sucesso_certidao');

        $refererUrl = $this->referer();
        $this->set(compact('sucesso','refererUrl'));

    }

    public function consultarAutenticidade() {

        // Dados do formulário
        $parametros = $this->request->getQuery(); // Obtém todos os parâmetros enviados na requisição
        $session = $this->getRequest()->getSession();
        $cod_autenticidade = $parametros['valor'];

        // Dados da API
        $config_servico = Configure::read('servico');

        $apiUrl = $config_servico['api_consulta_autenticidade']['url'];

        if ($this->request->is('get')) {

            // Verificar se já validou o recaptcha antes
            if($session->check('recaptcha_valid') && $session->read('recaptcha_valid') === true) {

                $client = new Client();

                // Enviar a requisição para a API com os parâmetros na URL 
                $response = $client->get($apiUrl, $parametros);

                $resultado = json_decode($response->getBody()->getContents(), true);

                if ($resultado === null) {
                    $this->request->getSession()->write('sucesso_certidao', false);
                    $this->redirect(['action' => 'index']);
                } else {
                    $this->request->getSession()->write('sucesso_certidao', true);
                    $this->set(compact('resultado','cod_autenticidade'));
                    $this->viewBuilder()->setOption('serialize', ['resultado']);                    
                }


                
            }else if($this->GoogleRecaptcha->verify($this->request->getQuery('g-recaptcha-response'),$this->request) == true){

                $session->write('recaptcha_valid', true);
                
                $client = new Client();

                // Enviar a requisição para a API com os parâmetros na URL 
                $response = $client->get($apiUrl, $parametros);
                
                $resultado = json_decode($response->getBody()->getContents(), true); 
   
                if ($resultado === null) {
                    $this->request->getSession()->write('sucesso_certidao', false);
                    $this->redirect(['action' => 'index']);
                } else {
                    $this->request->getSession()->write('sucesso_certidao', true);
                    $this->set(compact('resultado','cod_autenticidade'));
                    $this->viewBuilder()->setOption('serialize', ['resultado']);                    
                }
            }
            
        }
    }

    public function imprimirConsultaAutenticidade() {

        // Dados da API
        $client = new Client();
        $config_servico = Configure::read('servico');
        $apiUrl = $config_servico['api_imprime_consulta_autenticidade']['url'];
        $parametros = $this->request->getQuery();

        $response = $client->get($apiUrl, $parametros);

         // Obter o corpo da resposta da API como string
         $pdfContent = (string)$response->getBody();

         // Configurar o cabeçalho para indicar que a resposta é um PDF
         header('Content-Type: application/pdf');

         echo $pdfContent;

    }
 

}




