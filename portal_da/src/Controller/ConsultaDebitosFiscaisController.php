<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;
use Cake\Core\Configure;

class ConsultaDebitosFiscaisController extends AppController{

    public $opcoes = [
        'Selecione uma opção' => 'Selecione uma opção',
        'CPF' => 'CPF',
        'CNPJ' => 'CNPJ',
        'Certidão' => 'Certidão',
        'Taxa de Incêndio' => 'Taxa de Incêndio',
        'RENAVAM' => 'RENAVAM',
        'Processo Judicial novo' => 'Processo Judicial novo',
        'Processo Judicial antigo' => 'Processo Judicial antigo',
        'Processo Administrativo' => 'Processo Administrativo',
        'Auto de Infração' => 'Auto de Infração'
    ];

    public $opcoes_pa = [
        'E-' => 'E-',
        'E-66' => 'E-66',
        'E-77' => 'E-77',
        'E-88' => 'E-88',
        'E-99' => 'E-99',
        'SEI-' => 'SEI-',
        'IPS' => 'IPS'
    ];

    public function initialize(): void{

        parent::initialize();
        
        $this->loadComponent('Paginator');
        $this->loadComponent('GoogleRecaptcha');

        Configure::load('config_servico');
    }


    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */


    public function index(){

        $this->set('opcoes', $this->opcoes);
        $this->set('opcoes_pa', $this->opcoes_pa);

    }


    public function consultarDebitosFiscais(){

        // Dados do formulário
        $parametros = $this->request->getQuery(); // Obtém todos os parâmetros enviados na requisição
        $session = $this->getRequest()->getSession();

        // Dados da API
        $config_servico = Configure::read('servico');
        $apiUrl = $config_servico['consulta_debito']['url'];

        if ($this->request->is('get')) {

            //variaveis  usadas na View
            $campoEscolhido = $this->request->getQuery('campo');
            $campo = $this->opcoes[$campoEscolhido];
            $coluna = $this->getColuna($campo);      
            $campo_pa = $this->request->getQuery('campo_pa');
            $municipio_select = $this->request->getQuery('cidade_logradouro');
            $data_consulta = date('d/m/Y', strtotime('-1 day'));
            $valor = preg_replace('/[^0-9]/', '', $this->request->getQuery('valor'));

            //formatando saida dos numeros dos PAs
            $valor_pa_orgao = $this->request->getQuery('valor_pa_orgao');
            if ($campo_pa == 'E-') {
                $valor_pa_orgao = str_pad($valor_pa_orgao, 2, '0', STR_PAD_LEFT);
            }

            $valor_pa_unidade_protocoladora = $this->request->getQuery('valor_pa_unidade_protocoladora');
            if ($campo_pa == 'E-') {
                $valor_pa_unidade_protocoladora = str_pad($valor_pa_unidade_protocoladora, 3, '0', STR_PAD_LEFT);
            }else if($campo_pa == 'SEI-'){
                $valor_pa_unidade_protocoladora = str_pad($valor_pa_unidade_protocoladora, 6, '0', STR_PAD_LEFT);
            }

            $valor_pa_processo = $this->request->getQuery('valor_pa_processo');
            if ($campo_pa == 'E-' || $campo_pa == 'SEI-' || $campo_pa == 'IPS') {
                $valor_pa_processo = str_pad($valor_pa_processo, 6, '0', STR_PAD_LEFT);
            }else if ($campo_pa == 'E-66' || $campo_pa == 'E-77' || $campo_pa == 'E-88' || $campo_pa == 'E-99'){
                $valor_pa_processo = str_pad($valor_pa_processo, 11, '0', STR_PAD_LEFT);
            }

            $valor_pa_ano = $this->request->getQuery('valor_pa_ano');


            // Verificar se já validou o recaptcha antes
            if($session->check('recaptcha_valid') && $session->read('recaptcha_valid') === true) {

                $client = new Client();

                // Enviar a requisição para a API com os parâmetros na URL 
                $response = $client->get($apiUrl, $parametros);
              
                $cadastros = $response->getBody()->getContents();
     
                $this->set(compact('cadastros','data_consulta','campo','valor','valor_pa_orgao', 'valor_pa_unidade_protocoladora','valor_pa_processo','valor_pa_ano','campo_pa','municipio_select'));
                $this->viewBuilder()->setOption('serialize', ['cadastros','data_consulta']);
                
            }else if($this->GoogleRecaptcha->verify($this->request->getQuery('g-recaptcha-response'),$this->request) == true){

                $session->write('recaptcha_valid', true);

                $client = new Client();

                // Enviar a requisição para a API com os parâmetros na URL
                $response = $client->get($apiUrl, $parametros);
                               
                $cadastros = $response->getBody()->getContents();
               
                $this->set(compact('cadastros','data_consulta','campo','valor','valor_pa_orgao', 'valor_pa_unidade_protocoladora','valor_pa_processo','valor_pa_ano','campo_pa','municipio_select'));
                $this->viewBuilder()->setOption('serialize', ['cadastros','data_consulta']);

            }else {
                $this->Flash->error(__('Por favor, remarque o captcha para validar a consulta.<i class="material-icons icon-close">close</i>'), ['escape' => false]);
                $this->redirect($this->referer());
            }
        }

    }


    private function getColuna(string $campo): ? string{

        switch ($campo) {
            case 'CPF':
                return 'cpf_raizcnpj';
            case 'CNPJ':
                return 'cpf_raizcnpj';
            case 'Certidão':
                return 'numero_cda';
            case 'Taxa de Incêndio':
                return 'inscricao_predial';
            case 'RENAVAM':
                return 'renavam';
            case 'Processo Judicial novo':
                return 'cnj';
            case 'Processo Judicial antigo':
                return 'cnj_antigo';
            case 'Processo Administrativo':
                 return 'processo_administrativo';
            case 'Auto de Infração':
                return 'auto_infracao';
            default:
                return null;
        }
    }   

}
