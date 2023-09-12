<?php
declare(strict_types=1); 

namespace App\Controller;

use Exception;
use InvalidArgumentException;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Mailer;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * ApiSolicitacaoController Controller
 *
 * @method \App\Model\Entity\ApiSolicitacaoController[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */

class ApiSolicitacaoController extends AppController{

   
    const CONNECTION_ERROR_LIMIT = '5';

    public function initialize() : void {
        
        parent::initialize();

        $this->conexaoDefault = ConnectionManager::get('default');
        $this->conexaoPortalContribuinte = ConnectionManager::get('portal_contribuinte');
        $this->conexaoPessoaRf = ConnectionManager::get('pessoa_rf');

        $this->loadComponent('Pdf');


    }

    public function apiVerificaPessoaRf() {

        try{
            
            $parametros = $this->request->getQuery();
            $documento = preg_replace('/[^0-9]/', '', $parametros['valor']);

            $query = $this->conexaoPessoaRf->newQuery()
            ->select(['nome', 'cpf'])
            ->from('pessoa_fisica')
            ->where(['cpf' => $documento])
            ->execute()
            ->fetchAll('assoc');

            //Resposta da API
            $resultado = [
                'data' => $query,
                'documento' =>$documento
            ];      
                
            // Retorna a resposta como JSON
            $this->response = $this->response->withType('application/json')->withStringBody(json_encode($resultado));

            return $this->response;
           

        } catch (Exception $e) {

            $this->response = $this->response->withStatus(400)->withStringBody($e->getMessage());            
            return $this->response;

        } catch (InvalidArgumentException $e) {

            throw new InvalidArgumentException("Não foi possível executar esta requisição.432434");
            
        }
    }

    public function apiVerificaCnpjRf() {



        try{

            ini_set('max_execution_time', '60');
            
            $parametros = $this->request->getQuery();
            $documento = preg_replace('/[^0-9]/', '', $parametros['valor']);
            $raizCnpj = substr($documento, 0, 8);
            $raizCnpjSemZeros = ltrim($raizCnpj, '0'); 

            $query = $this->conexaoPessoaRf->newQuery()
            ->select(['cnpj_basico','nome_fantasia'])
            ->from('estabelecimento')
            ->where([
                'cnpj_basico' => $raizCnpjSemZeros,
                'nome_fantasia IS NOT' => null,
            ])
            ->limit(1)
            ->execute()
            ->fetch('assoc');

            //Resposta da API
            $resultado = [
                'data' => $query,
                'documento' =>$documento
            ];
                           
            // Retorna a resposta como JSON
            $this->response = $this->response->withType('application/json')->withStringBody(json_encode($resultado));

            return $this->response;
           

        } catch (Exception $e) {

            $this->response = $this->response->withStatus(400)->withStringBody($e->getMessage());            
            return $this->response;

        } catch (InvalidArgumentException $e) {

            throw new InvalidArgumentException("Não foi possível executar esta requisição.");
            
        }
    }

    
    public function apiSolicitaCertidao() {

        try{
            
            $parametros = $this->request->getQuery();


            //PESSOA FISICA
            // $parametros = [
            //     'tipo_contribuinte' => 'PESSOA FISICA',
            //     'nome_contribuinte' => 'FABRICIO BASTOS PEREIRA LINS',
            //     'valor' => '01321606680',
            //     'email' => 'barretopcarlos@gmail.com'
            // ];

            //PESSOA JURIDICA
            // $parametros = [
            //     'tipo_contribuinte' => 'PESSOA JURIDICA',
            //     'nome_contribuinte' => 'LUBEL MALHAS MED',
            //     'valor' => '01.894.189/0001-76',
            //     'email' => 'barretopcarlos@gmail.com',
            // ];

            // $parametros = [
            //     'tipo_contribuinte' => 'PESSOA JURIDICA',
            //     'nome_contribuinte' => 'ZINZANE',
            //     'valor' => '05.027.195/0001-87',
            //     'email' => 'barretopcarlos@gmail.com',
            // ];

                    
            // $parametros = [
            //     'tipo_contribuinte' => 'PESSOA JURIDICA',
            //     'nome_contribuinte' => 'ARTISTAS',
            //     'valor' => '99.017.782/0001-39',
            //     'email' => 'barretopcarlos@gmail.com',
            // ];
            
            $documento = preg_replace('/[^0-9]/', '', $parametros['valor']);
            
            if(isset($parametros['tipo_contribuinte']) && $parametros['tipo_contribuinte'] == 'PESSOA FISICA'){

                //Verifica se há solicitação no mesmo dia para pessoa fiica
                $solicitacao_existente = $this->verificaSolicitacaoExistentePF($documento);

                if($solicitacao_existente == false){
    
                    $resultado = $this->salvarSolicitacao($parametros);
    
                    $this->response = $this->response->withType('application/json')->withStringBody(json_encode($resultado));
    
                    return $this->response;
    
    
                }else{
                    
                    $resultado = $this->buscarSolicitacaoExistentePF($documento);
    
                    //Criando array associativo para corrigir problema da resposta na API - REFATORAR? 
                    $data = [
                        'solicitacao' => [
                            'id' => (int) $resultado['solicitacao']['id'],
                            'id_contribuinte_tipo' => $resultado['solicitacao']['id_contribuinte_tipo'],
                            'data_hora_solicitacao' => $resultado['solicitacao']['data_hora_solicitacao']->format('Y-m-d H:i:s'),
                            'id_certidao_tipo' => $resultado['solicitacao']['id_certidao_tipo'],
                            'contribuinte_cpf' => $resultado['solicitacao']['contribuinte_cpf'],
                            'contribuinte_cnpj' => $resultado['solicitacao']['contribuinte_cnpj'],
                            'contribuinte_nome' => $resultado['solicitacao']['contribuinte_nome'],
                            'solicitante_email' => $resultado['solicitacao']['solicitante_email']
                            
                        ],
                        'certidao' => [
                            'id' => (int) $resultado['certidao']['id'],
                            'cod_autenticidade_certidao' => $resultado['certidao']['cod_autenticidade_certidao'],
                            'id_solicitacao' => (int) $resultado['certidao']['id_solicitacao'],
                            'nome_contribuinte' => $resultado['certidao']['nome_contribuinte'],
                            'documento_contribuinte' => (int) $resultado['certidao']['documento_contribuinte'],
                            'data_hora_consulta' => $resultado['certidao']['data_hora_consulta']->format('Y-m-d H:i:s'),
                            'certidao_validade' => $resultado['certidao']['certidao_validade']->format('Y-m-d H:i:s'),
                        ],
                        
                        'existente' => $resultado['existente'],
                    ];
    
                    $this->response = $this->response->withType('application/json')->withStringBody(json_encode($data));
                    
                    return $this->response;
                    
                }          

            }elseif(isset($parametros['tipo_contribuinte']) && $parametros['tipo_contribuinte'] == 'PESSOA JURIDICA'){ /////////////////////////////////////////////////////////////////////////////////////

                //Verifica se há solicitação no mesmo dia para pessoa juridica
                $solicitacao_existente = $this->verificaSolicitacaoExistentePj($documento);

                if($solicitacao_existente == false){
    
                    $resultado = $this->salvarSolicitacaoPJ($parametros);

                    $this->response = $this->response->withType('application/json')->withStringBody(json_encode($resultado));
    
                    return $this->response;
    
    
                }else{
                    
                    $resultado = $this->buscarSolicitacaoExistentePJ($documento);
    
                    //Criando array associativo para corrigir problema da resposta na API - REFATORAR? 
                    $data = [
                        'solicitacao' => [
                            'id' => (int) $resultado['solicitacao']['id'],
                            'id_contribuinte_tipo' => $resultado['solicitacao']['id_contribuinte_tipo'],
                            'data_hora_solicitacao' => $resultado['solicitacao']['data_hora_solicitacao']->format('Y-m-d H:i:s'),
                            'id_certidao_tipo' => $resultado['solicitacao']['id_certidao_tipo'],
                            'contribuinte_cpf' => $resultado['solicitacao']['contribuinte_cpf'],
                            'contribuinte_cnpj' => $resultado['solicitacao']['contribuinte_cnpj'],
                            'contribuinte_nome' => $resultado['solicitacao']['contribuinte_nome'],
                            'solicitante_email' => $resultado['solicitacao']['solicitante_email']
                            
                        ],
                        'certidao' => [
                            'id' => (int) $resultado['certidao']['id'],
                            'cod_autenticidade_certidao' => $resultado['certidao']['cod_autenticidade_certidao'],
                            'id_solicitacao' => (int) $resultado['certidao']['id_solicitacao'],
                            'nome_contribuinte' => $resultado['certidao']['nome_contribuinte'],
                            'documento_contribuinte' => (int) $resultado['certidao']['documento_contribuinte'],
                            'data_hora_consulta' => $resultado['certidao']['data_hora_consulta']->format('Y-m-d H:i:s'),
                            'certidao_validade' => $resultado['certidao']['certidao_validade']->format('Y-m-d H:i:s'),
                        ],

                        'existente' => $resultado['existente'],
                    ];
    
                    $this->response = $this->response->withType('application/json')->withStringBody(json_encode($data));
                    
                    return $this->response;
                    
                }          

            }

           
        } catch (Exception $e) {

            $this->response = $this->response->withStatus(400)->withStringBody($e->getMessage());            
            return $this->response;

        } catch (InvalidArgumentException $e) {

            throw new InvalidArgumentException("Não foi possível executar esta requisição.");
            
        }
    }

    


    public function verificaSolicitacaoExistentePF($documento) {

        //verifica se há alguma solicitação no mesmo dia.
        $data_atual = date('Y-m-d');
        $data_inicio = $data_atual . ' 00:00:00';
        $data_fim = $data_atual . ' 23:59:59';
    
        $solicitacao = $this->conexaoPortalContribuinte->newQuery()
        ->select([
            'solicitacao.id'
        ])
        ->from('solicitacao')
        ->join([
            'table' => 'certidao_tipo',
            'alias' => 'CertidaoTipo',
            'type' => 'LEFT',
            'conditions' => [
                'CertidaoTipo.id = solicitacao.id_certidao_tipo'
            ]
        ])
        ->where([
            'contribuinte_cpf' => $documento,
            'data_hora_solicitacao BETWEEN :data_inicio AND :data_fim'
        ])
        ->bind(':data_inicio', $data_inicio, 'datetime')
        ->bind(':data_fim', $data_fim, 'datetime')
        ->execute()
        ->fetch('assoc');

        return $solicitacao;
    }

    public function verificaSolicitacaoExistentePj($documento) { //

        //verifica se há alguma solicitação no mesmo dia.
        $data_atual = date('Y-m-d');
        $data_inicio = $data_atual . ' 00:00:00';
        $data_fim = $data_atual . ' 23:59:59';
            
        $solicitacao = $this->conexaoPortalContribuinte->newQuery()
        ->select([
            'solicitacao.id'
        ])
        ->from('solicitacao')
        ->join([
            'table' => 'certidao_tipo',
            'alias' => 'CertidaoTipo',
            'type' => 'LEFT',
            'conditions' => [
                'CertidaoTipo.id = solicitacao.id_certidao_tipo'
            ]
        ])
        ->where([
            'contribuinte_cnpj' => $documento,
            'data_hora_solicitacao BETWEEN :data_inicio AND :data_fim'
        ])
        ->bind(':data_inicio', $data_inicio, 'datetime')
        ->bind(':data_fim', $data_fim, 'datetime')
        ->execute()
        ->fetch('assoc');

        return $solicitacao;
    }

    public function buscarSolicitacaoExistentePF($documento) {

        //Busca a solicitação e certidão existente para reimpressão

        $data_atual = date('Y-m-d');
        $data_inicio = $data_atual . ' 00:00:00';
        $data_fim = $data_atual . ' 23:59:59';

        $solicitacaoTable = TableRegistry::getTableLocator()->get('Solicitacao', ['connection' => $this->conexaoPortalContribuinte]);
        $certidaoTable = TableRegistry::getTableLocator()->get('Certidao', ['connection' => $this->conexaoPortalContribuinte]);
        
        $solicitacao = $solicitacaoTable
            ->find()
            ->where([
                'contribuinte_cpf' => $documento,
                'data_hora_solicitacao BETWEEN :data_inicio AND :data_fim',
            ])
            ->bind(':data_inicio', $data_inicio, 'datetime')
            ->bind(':data_fim', $data_fim, 'datetime')
            ->first();

        $certidao = $certidaoTable
            ->find()
            ->where([
                'documento_contribuinte' => $documento,
                'data_hora_consulta BETWEEN :data_inicio AND :data_fim',
            ])
            ->bind(':data_inicio', $data_inicio, 'datetime')
            ->bind(':data_fim', $data_fim, 'datetime')
            ->first();

        $entidades = [
            'solicitacao' => $solicitacao->toArray(),
            'certidao' => $certidao->toArray(),
            'existente' => true
        ];    

        return $entidades;
        
    }


    public function buscarSolicitacaoExistentePJ($documento) { //

        //Busca a solicitação e certidão existente para reimpressão

        $data_atual = date('Y-m-d');
        $data_inicio = $data_atual . ' 00:00:00';
        $data_fim = $data_atual . ' 23:59:59';

        $solicitacaoTable = TableRegistry::getTableLocator()->get('Solicitacao', ['connection' => $this->conexaoPortalContribuinte]);
        $certidaoTable = TableRegistry::getTableLocator()->get('Certidao', ['connection' => $this->conexaoPortalContribuinte]);
        
        $solicitacao = $solicitacaoTable
            ->find()
            ->where([
                'contribuinte_cnpj' => $documento,
                'data_hora_solicitacao BETWEEN :data_inicio AND :data_fim',
            ])
            ->bind(':data_inicio', $data_inicio, 'datetime')
            ->bind(':data_fim', $data_fim, 'datetime')
            ->first();

        $certidao = $certidaoTable
            ->find()
            ->where([
                'documento_contribuinte' => $documento,
                'data_hora_consulta BETWEEN :data_inicio AND :data_fim',
            ])
            ->bind(':data_inicio', $data_inicio, 'datetime')
            ->bind(':data_fim', $data_fim, 'datetime')
            ->first();

        $entidades = [
            'solicitacao' => $solicitacao->toArray(),
            'certidao' => $certidao->toArray(),
            'existente' => true
        ];    

        return $entidades;
        
    }
    
    
    public function salvarSolicitacao($parametros){  //

        
        // $parametros = [
        //     'tipo_contribuinte' => 'PESSOA FISICA',
        //     'nome_contribuinte' => 'Carlos Barreto',
        //     'valor' => '12039653708',
        //     'email' => 'barretopcarlos@gmail.com',
        // ];

        //documento para uso nas buscas de dividas e envolvidos
        $cpf_contribuinte = preg_replace('/[^0-9]/', '', $parametros['valor']);
        $email = $parametros['email'];

        //verificar se há divida na tabela cadastro.
        $divida_cadastro = $this->verificaDividaCadastro($cpf_contribuinte);

        //verificar se o documento está envolvido em alguma divida na tabela envolvidos.
        $divida_envolvido = $this->verificaEnvolvido($cpf_contribuinte);

        if($divida_cadastro != null || $divida_envolvido != null){
            $tipo_certidao = 1;
        }else{
            $tipo_certidao = 3;
        }

        $data_hora_solicitacao = date('Y-m-d H:i:s');
        $nome_contribuinte = $parametros['nome_contribuinte'];
        $tipo_contribuinte = 1;

        // Salvando Entity Solicitacao
        $solicitacaoTable = TableRegistry::getTableLocator()->get('Solicitacao', ['connection' => $this->conexaoPortalContribuinte]);
        $solicitacao = $solicitacaoTable->newEmptyEntity();
        $solicitacao->data_hora_solicitacao = $data_hora_solicitacao;
        $solicitacao->id_certidao_tipo = $tipo_certidao;
        $solicitacao->contribuinte_nome = $nome_contribuinte;
        $solicitacao->contribuinte_cpf = $cpf_contribuinte;
        $solicitacao->id_contribuinte_tipo = $tipo_contribuinte;
        $solicitacao->solicitante_email = $email;

        if ($solicitacaoTable->save($solicitacao)) {

            $id_solicitacao = $solicitacao->id;

            // montando código autenticidade 
            $random_numerico = rand(1, 9999);
            $random_numerico = str_pad(strval($random_numerico), 4, '0', STR_PAD_LEFT);

            $ano_atual = date('Y');

            $random_letras = '';
            for ($i = 0; $i < 4; $i++) {
                $random_letras .= chr(random_int(65, 90));
            }

            $cod_autenticidade_certidao = "{$random_numerico}-{$ano_atual}-{$random_letras}-{$id_solicitacao}";

            //ID da solicitação salva
            $id_solicitacao = $solicitacao->id;
            $data_hora_consulta = date('Y-m-d H:i:s');

            //calcular validade certidao

            $validade = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'dias_validade'
            ])
            ->from('certidao_validade')
            ->where([
                'id_certidao_tipo' => $tipo_certidao,
            ])
            ->execute()
            ->fetch('assoc');
    
            $dias_validade = intval($validade['dias_validade']);
            $data_hora_consulta_obj = FrozenTime::createFromFormat('Y-m-d H:i:s', $data_hora_consulta);
            // Adicionar os dias à data
            $validade = $data_hora_consulta_obj->addDays($dias_validade);
            // Formatar a nova data no mesmo formato
            $validade_certidao_banco = $validade->format('Y-m-d H:i:s');
            $validade_certidao_pdf = $validade->format('d/m/Y');

            //Gerando PDF da Certidão
            if($tipo_certidao == 1){
                $certidao_pdf = $this->gerarCertidaoPositivaPf($id_solicitacao,$nome_contribuinte,$cpf_contribuinte,$cod_autenticidade_certidao,$email,$validade_certidao_pdf,$dias_validade);
            }else if($tipo_certidao == 3){
                $certidao_pdf = $this->gerarCertidaoNegativaPf($id_solicitacao,$nome_contribuinte,$cpf_contribuinte,$cod_autenticidade_certidao,$email,$validade_certidao_pdf,$dias_validade);
            }

            // Salvando Entity Certidao
            $certidaoTable = TableRegistry::getTableLocator()->get('Certidao', ['connection' => $this->conexaoPortalContribuinte]);
            $certidao = $certidaoTable->newEmptyEntity();
            $certidao->id_solicitacao = $id_solicitacao;
            $certidao->cod_autenticidade_certidao = $cod_autenticidade_certidao;
            $certidao->nome_contribuinte = $nome_contribuinte;
            $certidao->documento_contribuinte = $cpf_contribuinte;
            $certidao->data_hora_consulta = $data_hora_consulta;
            $certidao->certidao_arquivo = $certidao_pdf;
            $certidao->certidao_validade = $validade_certidao_banco;

            if ($certidaoTable->save($certidao)) {

                unset($certidao->certidao_arquivo);

                $entidades = [
                    'solicitacao' => $solicitacao->toArray(),
                    'certidao' =>$certidao->toArray()
                ];   

                return $entidades;

            } else {

                echo 'NÃO SALVOU Certidao';
                exit;

            }

        } else {

            echo 'NÃO SALVOU Solicitacao';
            exit;

        }
    }

    public function salvarSolicitacaoPJ($parametros){ // 

        // //PESSOA JURIDICA
        //     $parametros = [
        //         'tipo_contribuinte' => 'PESSOA JURIDICA',
        //         'nome_contribuinte' => 'LUBEL MALHAS MED',
        //         'valor' => '12890886000175',
        //         'email' => 'barretopcarlos@gmail.com',
        //     ];

        // $parametros = [
        //     'tipo_contribuinte' => 'PESSOA JURIDICA',
        //     'nome_contribuinte' => 'ZINZANE',
        //     'valor' => '05.027.195/0001-87',
        //     'email' => 'barretopcarlos@gmail.com',
        // ];

        
        // $parametros = [
        //     'tipo_contribuinte' => 'PESSOA JURIDICA',
        //     'nome_contribuinte' => 'ARTISTAS',
        //     'valor' => '99.017.782/0001-39',
        //     'email' => 'barretopcarlos@gmail.com',
        // ];

        //documento para uso nas buscas de dividas e envolvidos
        $cnpj_contribuinte = preg_replace('/[^0-9]/', '', $parametros['valor']);
        $email = $parametros['email'];

        //verificar se há divida na tabela cadastro.
        $divida_cadastro = $this->verificaDividaCadastroPJ($cnpj_contribuinte);

        //verificar se o documento está envolvido em alguma divida na tabela envolvidos.
        $divida_envolvido = $this->verificaEnvolvidoPJ($cnpj_contribuinte);

        //verificar se o documento possui incorporações, se tiver verifica dividas dessas incorporações.
        $correlacionados = $this->verificaCorrelacaoPj($cnpj_contribuinte);

        $correlacionados_cadastro = $correlacionados['dividas_correlacionadas_cadastro'];
        $correlacionados_envolvidos = $correlacionados['dividas_correlacionadas_envolvidos'];

        if($divida_cadastro != null || $divida_envolvido != null || $correlacionados_cadastro != null || $correlacionados_envolvidos != null){
            $tipo_certidao = 2;
        }else{
            $tipo_certidao = 4;
        }

        $data_hora_solicitacao = date('Y-m-d H:i:s');
        $nome_contribuinte = $parametros['nome_contribuinte'];
        $tipo_contribuinte = 2;

        // Salvando Entity Solicitacao
        $solicitacaoTable = TableRegistry::getTableLocator()->get('Solicitacao', ['connection' => $this->conexaoPortalContribuinte]);
        $solicitacao = $solicitacaoTable->newEmptyEntity();
        $solicitacao->data_hora_solicitacao = $data_hora_solicitacao;
        $solicitacao->id_certidao_tipo = $tipo_certidao;
        $solicitacao->contribuinte_nome = $nome_contribuinte;
        $solicitacao->contribuinte_cnpj = $cnpj_contribuinte;
        $solicitacao->id_contribuinte_tipo = $tipo_contribuinte;
        $solicitacao->solicitante_email = $email;

        if ($solicitacaoTable->save($solicitacao)) {

            $id_solicitacao = $solicitacao->id;

            // montando código autenticidade 
            $random_numerico = rand(1, 9999);
            $random_numerico = str_pad(strval($random_numerico), 4, '0', STR_PAD_LEFT);

            $ano_atual = date('Y');

            $random_letras = '';
            for ($i = 0; $i < 4; $i++) {
                $random_letras .= chr(random_int(65, 90));
            }

            $cod_autenticidade_certidao = "{$random_numerico}-{$ano_atual}-{$random_letras}-{$id_solicitacao}";

            //ID da solicitação salva
            $id_solicitacao = $solicitacao->id;
            $data_hora_consulta = date('Y-m-d H:i:s');

            //calcular validade certidao

            $validade = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'dias_validade'
            ])
            ->from('certidao_validade')
            ->where([
                'id_certidao_tipo' => $tipo_certidao,
            ])
            ->execute()
            ->fetch('assoc');
    
            $dias_validade = intval($validade['dias_validade']);
            $data_hora_consulta_obj = FrozenTime::createFromFormat('Y-m-d H:i:s', $data_hora_consulta);
            // Adicionar os dias à data
            $validade = $data_hora_consulta_obj->addDays($dias_validade);
            // Formatar a nova data no mesmo formato
            $validade_certidao_banco = $validade->format('Y-m-d H:i:s');
            $validade_certidao_pdf = $validade->format('d/m/Y');

            //Gerando PDF da Certidão
            if($tipo_certidao == 2){
                $certidao_pdf = $this->gerarCertidaoPositivaPJ($id_solicitacao,$nome_contribuinte,$cnpj_contribuinte,$cod_autenticidade_certidao,$email,$validade_certidao_pdf,$dias_validade);
            }else if($tipo_certidao == 4){
                $certidao_pdf = $this->gerarCertidaoNegativaPJ($id_solicitacao,$nome_contribuinte,$cnpj_contribuinte,$cod_autenticidade_certidao,$email,$validade_certidao_pdf,$dias_validade);
            }

            // Salvando Entity Certidao
            $certidaoTable = TableRegistry::getTableLocator()->get('Certidao', ['connection' => $this->conexaoPortalContribuinte]);
            $certidao = $certidaoTable->newEmptyEntity();
            $certidao->id_solicitacao = $id_solicitacao;
            $certidao->cod_autenticidade_certidao = $cod_autenticidade_certidao;
            $certidao->nome_contribuinte = $nome_contribuinte;
            $certidao->documento_contribuinte = $cnpj_contribuinte;
            $certidao->data_hora_consulta = $data_hora_consulta;
            $certidao->certidao_arquivo = $certidao_pdf;
            $certidao->certidao_validade = $validade_certidao_banco;


            if ($certidaoTable->save($certidao)) {

                unset($certidao->certidao_arquivo);

                $entidades = [
                    'solicitacao' => $solicitacao->toArray(),
                    'certidao' =>$certidao->toArray()
                ];   

                return $entidades;


            } else {

                debug('NÃO SALVOU Certidao');
                exit;

            }

        } else {

            debug('NÃO SALVOU Solicitacao');
            exit;

        }
    }


    public function verificaDividaCadastro($documento){

        $documento = preg_replace('/[^0-9]/', '', $documento);

        $cadastros = $this->getTableLocator()->get('Cadastro')
                    ->find()
                    ->select([
                        'numero_documento_principal',
                        'numero_cda',
                        'Natureza.nome',
                        'codigo_natureza',
                        ])
                    ->where(function ($exp, $q) use ($documento) {
                        return $exp->like('numero_documento_principal', $documento)
                        ->isNull('data_cancelamento');
                    })
                    ->innerJoinWith('Natureza')
                    ->contain([
                        'Natureza' => [
                            'fields' => [
                                'codigo',
                                'nome',
                            ],
                            'strategy' => 'select'
                        ]
                        ])
                        ->toArray();

        return $cadastros;
        
    }

    public function verificaDividaCadastroPJ($documento){

        $documento = preg_replace('/[^0-9]/', '', $documento);
        $documento = substr($documento, 0, 8); //pegando a raiz

        $cadastros = $this->getTableLocator()->get('Cadastro')
                    ->find()
                    ->select([
                        'cpf_raizcnpj',
                        'numero_cda',
                        'Natureza.nome',
                        'codigo_natureza',
                        'nome_devedor',
                        'numero_documento_principal'
                        ])
                    ->where(function ($exp, $q) use ($documento) {
                        return $exp->like('cpf_raizcnpj', $documento)
                        ->isNull('data_cancelamento');
                    })
                    ->innerJoinWith('Natureza')
                    ->contain([
                        'Natureza' => [
                            'fields' => [
                                'codigo',
                                'nome',
                            ],
                            'strategy' => 'select'
                        ]
                        ])
                        ->toArray();

        return $cadastros;
        
    }

    

    public function verificaEnvolvido($documento){
        
        $documento = ltrim($documento, '0');

        $resultado = $this->getTableLocator()->get('Cadastro')
        ->find()->select([
            'Natureza.nome',
            'Cadastro.codigo_natureza',
            'Cadastro.cpf_raizcnpj',
            'Envolvidos.cnpj_cpf',
            'Envolvidos.nome_envolvido',
	        'Envolvidos.numero_cda',
            'Cadastro.numero_cda'
        ])
        ->from(['Cadastro' => 'cadastro'])
        ->innerJoin(['Natureza' => 'natureza'], [
            'Cadastro.codigo_natureza = Natureza.codigo'
        ])
        ->innerJoin(['Envolvidos' => 'rdg_envolvidos'], [
            'Cadastro.numero_cda = Envolvidos.numero_cda'
        ])
        ->where([
            'Envolvidos.cnpj_cpf LIKE' => $documento,
            'Cadastro.data_cancelamento IS NULL'
        ]);
        
        // Executa a consulta e obtém o resultado como um array
        $resultado = $resultado->all()->toArray();

        return $resultado;
        
    }

    public function verificaEnvolvidoPj($documento){

        $documento = preg_replace('/[^0-9]/', '', $documento);
        $documento = substr($documento, 0, 8); //pegando a raiz
        $documento = ltrim($documento, '0'); //retirando zeros a esquerda
        

        $resultado = $this->getTableLocator()->get('Cadastro')
        ->find()->select([
            'Natureza.nome',
            'Cadastro.codigo_natureza',
            'Cadastro.cpf_raizcnpj',
            'Envolvidos.cnpj_cpf',
            'Envolvidos.nome_envolvido',
	        'Envolvidos.numero_cda',
            'Cadastro.numero_cda',
            'Cadastro.nome_devedor',
            'Cadastro.numero_documento_principal'
        ])
        ->from(['Cadastro' => 'cadastro'])
        ->innerJoin(['Natureza' => 'natureza'], [
            'Cadastro.codigo_natureza = Natureza.codigo'
        ])
        ->innerJoin(['Envolvidos' => 'rdg_envolvidos'], [
            'Cadastro.numero_cda = Envolvidos.numero_cda'
        ])
        ->where([
            'Envolvidos.cnpj_cpf LIKE' => $documento,
            'Cadastro.data_cancelamento IS NULL'
        ]);
        
        // Executa a consulta e obtém o resultado como um array
        $resultado = $resultado->all()->toArray();

        return $resultado;
        
    }

    public function verificaCorrelacaoPj($documento){ //

        $documento = preg_replace('/[^0-9]/', '', $documento);
        $raiz_cnpj = substr($documento, 0, 8); //pegando a raiz
        
        $CorrelacaoTable = TableRegistry::getTableLocator()->get('CorrelacaoEmpresas', ['connection' => $this->conexaoPessoaRf]);

        // Buscando os CNPJ relacionados ao principal (contribuinte da pesquisa)
        $cnpj_correlacionados_relacionado = $CorrelacaoTable
            ->find()
            ->select(['cnpj_basico_relacionado'])
            ->where(['cnpj_basico_principal LIKE' => $raiz_cnpj])
            ->toArray();

        // Fazendo a busca inversa para saber se o CNPJ digitado é o relacionado de alguém e pegar esse 
        $cnpj_correlacionados_principal = $CorrelacaoTable
            ->find()
            ->select(['cnpj_basico_principal'])
            ->where(['cnpj_basico_relacionado LIKE' => $raiz_cnpj])
            ->toArray();

            // JUNTANDO TODAS OS REGISTROS ENCONTRADOS EM UM UNICO ARRAY
            $obj_correlacionados_relacionado = array_map(function ($entry) {
                return (object) $entry;
            }, $cnpj_correlacionados_relacionado);

            $obj_correlacionado_principal = array_map(function ($entry) {
                return (object) $entry;
            }, $cnpj_correlacionados_principal);

            $array_correlacionados = [];

            // CRIANDO UM NOVO OBJETO PARA UNIFICAR OS CNPJS ENCONTRADOS E CRIAR UMA CHAVE UNICA
            foreach ($obj_correlacionados_relacionado as $correlacionado) {
                $new_correlacionado = new \Cake\ORM\Entity();
                $new_correlacionado->cnpj_relacionado = $correlacionado->cnpj_basico_relacionado;
                $array_correlacionados[] = $new_correlacionado; 
            }

            foreach ($obj_correlacionado_principal as $principal) {
                $new_principal = new \Cake\ORM\Entity();
                $new_principal->cnpj_relacionado = $principal->cnpj_basico_principal;
                $array_correlacionados[] = $new_principal; 
            }
                   
        if($array_correlacionados != null){

            foreach ($array_correlacionados as $correlacao) {

                $dividas_correlacionadas_cadastro = $this->verificaDividaCadastroPj($correlacao['cnpj_relacionado']);
                $dividas_correlacionadas_envolvidos = $this->verificaEnvolvidoPj($correlacao['cnpj_relacionado']);
    
            } 
            
            $resultado = [
                'dividas_correlacionadas_cadastro' => $dividas_correlacionadas_cadastro,
                'dividas_correlacionadas_envolvidos' => $dividas_correlacionadas_envolvidos
            ];

            return $resultado;

        }else{

            $resultado = [
                'dividas_correlacionadas_cadastro' => [],
                'dividas_correlacionadas_envolvidos' => []
            ];

            return $resultado;
        }


        
          
    }    


    public function gerarCertidaoNegativaPf($solicitacao,$nome, $documento,$cod_autenticidade_certidao,$destinatario,$validade_formatada,$dias_validade){

        $solicitacao = strval($solicitacao);
        $documento = strval($documento);
        $data_atual = date('d/m/Y');
        $hora_atual = date('H:i');
        $hora_atual = date('H\hi', strtotime($hora_atual));
        $tipo_certidao = 'Certidão Negativa';

        date_default_timezone_set('America/Sao_Paulo');
        $meses_em_portugues = array(
            'January' => 'janeiro',
            'February' => 'fevereiro',
            'March' => 'março',
            'April' => 'abril',
            'May' => 'maio',
            'June' => 'junho',
            'July' => 'julho',
            'August' => 'agosto',
            'September' => 'setembro',
            'October' => 'outubro',
            'November' => 'novembro',
            'December' => 'dezembro'
        );
        $data_escrita = date('d \d\e ') . $meses_em_portugues[date('F')] . date(' \d\e Y'); 

        

        $cpf = $documento;
        $cpf_formatado = substr_replace($cpf, '.', 3, 0);
        $cpf_formatado = substr_replace($cpf_formatado, '.', 7, 0);
        $cpf_formatado = substr_replace($cpf_formatado, '-', 11, 0);

        $assinaturaResponsavelTable = TableRegistry::getTableLocator()->get('AssinaturaResponsavel', ['connection' => $this->conexaoPortalContribuinte]);
        $dados_procurador = $assinaturaResponsavelTable->find()->select(['nome_responsavel_assinatura','cargo_responsavel_assinatura'])->first();

        $link_qr = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'link'
            ])
            ->from('link_parametro')
            ->where([
                'id_link_tipo' => 3,
                'id_ambiente' => 1,
            ])
            ->execute()
            ->fetch('assoc');

        $linkQrCode = $link_qr['link'];


        $link_info = $this->conexaoPortalContribuinte->newQuery()
        ->select([
            'link'
        ])
        ->from('link_parametro')
        ->where([
            'id_link_tipo' => 2,
            'id_ambiente' => 1,
        ])
        ->execute()
        ->fetch('assoc');

        $linkInfo = $link_info['link'];
        

        $content = '
        <h1 style="text-align: center; font-size: 14px;">CERTIDÃO NEGATIVA DE DÉBITOS EM DÍVIDA ATIVA</h1>
        

        <p style="text-align: justify;">Certifico que, em consulta ao Sistema da Dívida Ativa no dia data_atual, em referência à solicitação nº <b>id_solicitacao</b>, <b>NÃO CONSTA DÉBITO INSCRITO</b> em Dívida Ativa para o CPF ou CNPJ informado abaixo:</p>
       

        <p><b>NOME:</b> nome_contribuinte</p>
        <p><b>CPF:</b> documento_contribuinte</p>

       
        <p style="text-align: justify;">A certidão negativa de Dívida Ativa e a certidão negativa de ICMS ou a certidão para não contribuinte do ICMS somente terão validade quando apresentadas em conjunto.</p>
        <p style="text-align: justify;">Os dados apresentados nesta certidão baseiam-se em pesquisa realizada a partir do CPF ou CNPJ fornecido no momento da apresentação do requerimento.</p>
        <p style="text-align: justify;">Fica ressalvado o direito da Fazenda Estadual de inscrever e cobrar débitos que vierem a ser apurados posteriormente à emissão da presente certidão.</p>
        <p style="text-align: justify;">A aceitação desta certidão está condicionada a verificação de sua autenticidade na INTERNET, no endereço: <a href="linkQrCode">linkQrCode</a></p>
        

        <p><b>CÓDIGO CERTIDÃO:</b> cod_autenticidade_certidao
        <p><b>PESQUISA CADASTRAL</b> realizada em: data_atual às hora_atualmin</p>
        

        <p style="text-align: justify;">Esta certidão tem validade até data_final_validade, considerando validade_certidao dias após a pesquisa cadastral realizada na data e hora acima, conforme artigo 11 da Resolução nº 2690 de 05/10/2009.</p>
        <p>Para maiores informações: <a href="linkInfo">linkInfo</a></p>

        <br>

        <p style="font-size:10px; text-align:center; margin-top:-15px !important; ">Rio de Janeiro, data_escrita.<br>
        nome_procurador<br>
        cargo_procurador PG-05</p>
        
        <p style="font-size:8px; text-align:center;">Emitida em data_atual às hora_atualmin</p>
        ';
        
        //SUBSTITUIR POR PARAMETROS DO CONTRIBUINTE
        $content = str_replace('nome_contribuinte', $nome, $content);
        $content = str_replace('documento_contribuinte', $cpf_formatado, $content);
        $content = str_replace('id_solicitacao', $solicitacao, $content);
        $content = str_replace('data_atual', $data_atual, $content);
        $content = str_replace('hora_atual', $hora_atual, $content);
        $content = str_replace('cod_autenticidade_certidao', $cod_autenticidade_certidao, $content);
        $content = str_replace('validade_certidao', strval($dias_validade), $content);
        $content = str_replace('data_final_validade', $validade_formatada, $content);
        $content = str_replace('nome_procurador', $dados_procurador['nome_responsavel_assinatura'], $content);
        $content = str_replace('cargo_procurador', $dados_procurador['cargo_responsavel_assinatura'], $content);
        $content = str_replace('data_escrita', $data_escrita, $content);
        $content = str_replace('linkQrCode', $linkQrCode, $content);
        $content = str_replace('linkInfo', $linkInfo, $content);
        
        $arquivo = $this->Pdf->gerarPdfCertidaoNegativa($content,$cod_autenticidade_certidao,$data_atual,$hora_atual);

        //gera o nome de arquivo aleatório e especifico o caminho
        $nomeArquivoTemporario = 'temporario_' . uniqid() . '.pdf';
        $caminhoDestino = WWW_ROOT . 'pdf/' . $nomeArquivoTemporario;

        // Grava PDF em diretório para a função de envio de email ler (este arquivo é excluido após o envio)
        file_put_contents($caminhoDestino, $arquivo);

        //enviar arquivo por email
        $this->enviarCertidaoEmail($destinatario, $cod_autenticidade_certidao, $nome, $tipo_certidao,$caminhoDestino,$solicitacao);
        
        // Deletando arquivo após envio do email.    
        unlink($caminhoDestino);

        return $arquivo;

    }


    public function gerarCertidaoNegativaPj($solicitacao,$nome, $documento,$cod_autenticidade_certidao,$destinatario,$validade_formatada,$dias_validade){

        $solicitacao = strval($solicitacao);
        $documento = strval($documento);
        $data_atual = date('d/m/Y');
        $hora_atual = date('H:i');
        $hora_atual = date('H\hi', strtotime($hora_atual));
        $tipo_certidao = 'Certidão Negativa';

        date_default_timezone_set('America/Sao_Paulo');
        $meses_em_portugues = array(
            'January' => 'janeiro',
            'February' => 'fevereiro',
            'March' => 'março',
            'April' => 'abril',
            'May' => 'maio',
            'June' => 'junho',
            'July' => 'julho',
            'August' => 'agosto',
            'September' => 'setembro',
            'October' => 'outubro',
            'November' => 'novembro',
            'December' => 'dezembro'
        );
        $data_escrita = date('d \d\e ') . $meses_em_portugues[date('F')] . date(' \d\e Y'); 

        $assinaturaResponsavelTable = TableRegistry::getTableLocator()->get('AssinaturaResponsavel', ['connection' => $this->conexaoPortalContribuinte]);
        $dados_procurador = $assinaturaResponsavelTable->find()->select(['nome_responsavel_assinatura','cargo_responsavel_assinatura'])->first();
        
        $documento = preg_replace('/[^0-9]/', '', $documento);
        $cnpj_formatado = substr_replace($documento, '.', 2, 0);
        $cnpj_formatado = substr_replace($cnpj_formatado, '.', 6, 0);
        $cnpj_formatado = substr_replace($cnpj_formatado, '/', 10, 0);
        $cnpj_formatado = substr_replace($cnpj_formatado, '-', 15, 0);

        $link_qr = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'link'
            ])
            ->from('link_parametro')
            ->where([
                'id_link_tipo' => 3,
                'id_ambiente' => 1,
            ])
            ->execute()
            ->fetch('assoc');

        $linkQrCode = $link_qr['link'];


        $link_info = $this->conexaoPortalContribuinte->newQuery()
        ->select([
            'link'
        ])
        ->from('link_parametro')
        ->where([
            'id_link_tipo' => 2,
            'id_ambiente' => 1,
        ])
        ->execute()
        ->fetch('assoc');

        $linkInfo = $link_info['link'];
        
        

        $content = '
        <h1 style="text-align: center; font-size: 14px;">CERTIDÃO NEGATIVA DE DÉBITOS EM DÍVIDA ATIVA</h1>
        

        <p style="text-align: justify;">Certifico que, em consulta ao Sistema da Dívida Ativa no dia data_atual, em referência à solicitação nº <b>id_solicitacao</b>, <b>NÃO CONSTA DÉBITO INSCRITO</b> em Dívida Ativa para o CNPJ informado abaixo:</p>
       
        <p><b>NOME:</b> nome_contribuinte</p>
        <p><b>CNPJ:</b> documento_contribuinte</p>
       
        <p style="text-align: justify;">A certidão negativa de Dívida Ativa e a certidão negativa de ICMS ou a certidão para não contribuinte do ICMS somente terão validade quando apresentadas em conjunto.</p>
        <p style="text-align: justify;">Os dados apresentados nesta certidão baseiam-se em pesquisa realizada a partir do CNPJ fornecido no momento da apresentação do requerimento.</p>
        <p style="text-align: justify;">Fica ressalvado o direito da Fazenda Estadual de inscrever e cobrar débitos que vierem a ser apurados posteriormente à emissão da presente certidão.</p>
        <p style="text-align: justify;">A aceitação desta certidão está condicionada a verificação de sua autenticidade na INTERNET, no endereço: <a href="linkQrCode">linkQrCode</a></p>
        

        <p><b>CÓDIGO CERTIDÃO:</b> cod_autenticidade_certidao
        <p><b>PESQUISA CADASTRAL</b> realizada em: data_atual às hora_atualmin</p>
        

        <p style="text-align: justify;">Esta certidão tem validade até data_final_validade, considerando validade_certidao dias após a pesquisa cadastral realizada na data e hora acima, conforme artigo 11 da Resolução nº 2690 de 05/10/2009.</p>
        <p>Para maiores informações: <a href="linkInfo">linkInfo</a></p>

            <br>
        
        <p style="font-size:10px; text-align:center;">Rio de Janeiro, data_escrita.<br>
        nome_procurador<br>
        cargo_procurador PG-05</p>

        <br><br><br>

        <p style="font-size:8px; text-align:center;">Emitida em data_atual às hora_atualmin</p>
        ';
        
        //SUBSTITUIR POR PARAMETROS DO CONTRIBUINTE
        $content = str_replace('nome_contribuinte', $nome, $content);
        $content = str_replace('documento_contribuinte', $cnpj_formatado, $content);
        $content = str_replace('id_solicitacao', $solicitacao, $content);
        $content = str_replace('data_atual', $data_atual, $content);
        $content = str_replace('hora_atual', $hora_atual, $content);
        $content = str_replace('cod_autenticidade_certidao', $cod_autenticidade_certidao, $content);
        $content = str_replace('validade_certidao', strval($dias_validade), $content);
        $content = str_replace('data_final_validade', $validade_formatada, $content);
        $content = str_replace('nome_procurador', $dados_procurador['nome_responsavel_assinatura'], $content);
        $content = str_replace('cargo_procurador', $dados_procurador['cargo_responsavel_assinatura'], $content);
        $content = str_replace('data_escrita', $data_escrita, $content);
        $content = str_replace('linkQrCode', $linkQrCode, $content);
        $content = str_replace('linkInfo', $linkInfo, $content);
        
        $arquivo = $this->Pdf->gerarPdfCertidaoNegativa($content,$cod_autenticidade_certidao,$data_atual,$hora_atual);

        //gera o nome de arquivo aleatório e especifico o caminho
        $nomeArquivoTemporario = 'temporario_' . uniqid() . '.pdf';
        $caminhoDestino = WWW_ROOT . 'pdf/' . $nomeArquivoTemporario;

        // Grava PDF em diretório para a função de envio de email ler (este arquivo é excluido após o envio)
        file_put_contents($caminhoDestino, $arquivo);

        //enviar arquivo por email
        $this->enviarCertidaoEmail($destinatario, $cod_autenticidade_certidao, $nome, $tipo_certidao,$caminhoDestino,$solicitacao);
        
        // Deletando arquivo após envio do email.    
        unlink($caminhoDestino);

        return $arquivo;

    }



    public function gerarCertidaoPositivaPf($solicitacao,$nome, $documento,$cod_autenticidade_certidao,$destinatario,$validade_certidao,$dias_validade){ 


        $dividas = $this->verificaDividaCadastro($documento);
        $envolvidos = $this->verificaEnvolvido($documento);
        $qtd_dividas = strval(count($this->verificaDividaCadastro($documento)));
        $qtd_envolvido = strval(count($this->verificaEnvolvido($documento)));

        $assinaturaResponsavelTable = TableRegistry::getTableLocator()->get('AssinaturaResponsavel', ['connection' => $this->conexaoPortalContribuinte]);
        $dados_procurador = $assinaturaResponsavelTable->find()->select(['nome_responsavel_assinatura','cargo_responsavel_assinatura'])->first();

        $solicitacao = strval($solicitacao);
        $documento = strval($documento);
        $data_atual = date('d/m/Y');
        $hora_atual = date('H:i');
        $hora_atual = date('H\hi', strtotime($hora_atual));
        $tipo_certidao = 'Certidão Positiva';

        date_default_timezone_set('America/Sao_Paulo');
        $meses_em_portugues = array(
            'January' => 'janeiro',
            'February' => 'fevereiro',
            'March' => 'março',
            'April' => 'abril',
            'May' => 'maio',
            'June' => 'junho',
            'July' => 'julho',
            'August' => 'agosto',
            'September' => 'setembro',
            'October' => 'outubro',
            'November' => 'novembro',
            'December' => 'dezembro'
        );
        $data_escrita = date('d \d\e ') . $meses_em_portugues[date('F')] . date(' \d\e Y'); 

        $link_qr = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'link'
            ])
            ->from('link_parametro')
            ->where([
                'id_link_tipo' => 3,
                'id_ambiente' => 1,
            ])
            ->execute()
            ->fetch('assoc');

        $linkQrCode = $link_qr['link'];

        
        $link_info = $this->conexaoPortalContribuinte->newQuery()
        ->select([
            'link'
        ])
        ->from('link_parametro')
        ->where([
            'id_link_tipo' => 2,
            'id_ambiente' => 1,
        ])
        ->execute()
        ->fetch('assoc');

        $linkInfo = $link_info['link'];


        $cpf = $documento;
        $cpf_formatado = substr_replace($cpf, '.', 3, 0);
        $cpf_formatado = substr_replace($cpf_formatado, '.', 7, 0);
        $cpf_formatado = substr_replace($cpf_formatado, '-', 11, 0);

        $funcionarioTable = TableRegistry::getTableLocator()->get('Funcionario', ['connection' => $this->conexaoPortalContribuinte]);
        $dados_procurador = $funcionarioTable->find()->select(['nome','cargo','especializada'])->first();

        $content = '
        <h1 style="text-align: center; font-size: 14px;">CERTIDÃO POSITIVA DE DÉBITOS EM DÍVIDA ATIVA</h1>
        

        <p style="text-align: justify;">Certifico, tendo em vista as informações fornecidas pelo Sistema da Dívida Ativa,  que no período de 1977 até data_atual, <b>CONSTA(M) qtd_dividas DÉBITO(S)</b>, relacionado(s) ao CPF documento_contribuinte, corporificados nas inscrições listadas no relatório de pesquisa cadastral em anexo, extraído do Sistema da Dívida Ativa.</p>
        <p style="text-align: justify;">A aceitação desta certidão está condicionada a verificação de sua autenticidade na INTERNET, no endereço: <a href="linkQrCode">linkQrCode</a></p>

       
        <p><b>NOME:</b> nome_contribuinte</p>
        <p><b>CPF:</b> documento_contribuinte</p>

      
        <p style="text-align: justify;">Foram localizados para o CPF pesquisado, o registro de envolvimento ou corresponsabilidade em <b>qtd_envolvido débito(s)</b>, corporificados nas inscrições listadas no relatório de pesquisa cadastral em anexo, extraído do Sistema da Dívida Ativa.</p>
        
        <p style="text-align: justify;">A presente certidão, lavrada em 01 (uma) lauda e [numero_laudas] lauda(s) de anexo, todas com informações somente no anverso, tem validade até o dia data_final_validade considerando o prazo de validade_certidao dias após sua emissão.</p>

        <p><b>CÓDIGO CERTIDÃO:</b> cod_autenticidade_certidao
        <p><b>PESQUISA CADASTRAL</b> realizada em: data_atual às hora_atualmin</p>
        

        <p style="text-align: justify;">Esta certidão tem validade até data_final_validade, considerando validade_certidao dias após a pesquisa cadastral realizada na data e hora acima, conforme artigo 11 da Resolução nº 2690 de 05/10/2009.</p>
        <p >Para maiores informações: <a href="linkInfo">linkInfo</a></p>
        
        <br>
    
        <p style="font-size:10px; text-align:center;">Rio de Janeiro, data_escrita.<br>
        nome_procurador<br>
        cargo_procurador PG-05</p>

        <p style="font-size:8px; text-align:center;">Emitida em data_atual às hora_atualmin</p>
        ';
        
        //SUBSTITUIR POR PARAMETROS DO CONTRIBUINTE
        $content = str_replace('nome_contribuinte', $nome, $content);
        $content = str_replace('documento_contribuinte', $cpf_formatado, $content);
        $content = str_replace('id_solicitacao', $solicitacao, $content);
        $content = str_replace('data_atual', $data_atual, $content);
        $content = str_replace('hora_atual', $hora_atual, $content);
        $content = str_replace('cod_autenticidade_certidao', $cod_autenticidade_certidao, $content);
        $content = str_replace('validade_certidao', strval($dias_validade), $content);
        $content = str_replace('data_final_validade', $validade_certidao, $content);
        $content = str_replace('qtd_dividas', $qtd_dividas, $content);
        $content = str_replace('qtd_envolvido', $qtd_envolvido, $content);
        $content = str_replace('nome_procurador', $dados_procurador['nome_responsavel_assinatura'], $content);
        $content = str_replace('cargo_procurador', $dados_procurador['cargo_responsavel_assinatura'], $content);
        $content = str_replace('data_escrita', $data_escrita, $content);
        $content = str_replace('linkQrCode', $linkQrCode, $content);
        $content = str_replace('linkInfo', $linkInfo, $content);


        $arquivo = $this->Pdf->gerarPdfCertidaoPositiva($content,$cod_autenticidade_certidao,$dividas,$envolvidos,$data_atual,$hora_atual);
        
        
        //gera o nome de arquivo aleatório e especifico o caminho
        $nomeArquivoTemporario = 'temporario_' . uniqid() . '.pdf';
        $caminhoDestino = WWW_ROOT . 'pdf/' . $nomeArquivoTemporario;

        // Grava PDF em diretório para a função de envio de email ler (este arquivo é excluido após o envio)
        file_put_contents($caminhoDestino, $arquivo);

        //enviar arquivo por email
        $this->enviarCertidaoEmail($destinatario, $cod_autenticidade_certidao, $nome, $tipo_certidao,$caminhoDestino,$solicitacao);
        
        // Deletando arquivo após envio do email.    
        unlink($caminhoDestino);

        return $arquivo;

    }


    public function gerarCertidaoPositivaPj($solicitacao,$nome, $documento,$cod_autenticidade_certidao,$destinatario,$validade_certidao,$dias_validade){ //

        // $solicitacao = '999';
        // $nome = 'Teste';
        // $documento = '05027195000187';
        // $cod_autenticidade_certidao ='4728-2023-NCHM-2';
        // $destinatario = 'carlos.barreto@extremedigital.com.br';
        // $validade_certidao = '26/04/2024';
        // $dias_validade = '180';

        $dividas = $this->verificaDividaCadastroPj($documento);
        $envolvidos = $this->verificaEnvolvidoPj($documento);
        $correlacionados = $this->verificaCorrelacaoPj($documento);
        $correlacionados_cadastro = $correlacionados['dividas_correlacionadas_cadastro'];
        $correlacionados_envolvidos = $correlacionados['dividas_correlacionadas_envolvidos'];
        $qtd_dividas = strval(count($dividas));
        $qtd_envolvido = strval(count($envolvidos));
        $qtd_correlacionados_cadastro = strval(count($correlacionados_cadastro));
        $qtd_correlacionados_envolvidos = strval(count($correlacionados_envolvidos));
        $qtd_dividas_correlacionadas = $qtd_correlacionados_cadastro + $qtd_correlacionados_envolvidos;
        $qtd_correlacionadas = strval($qtd_dividas_correlacionadas);

        $dividas_correlacionadas = [];

        if($correlacionados_cadastro == null && $correlacionados_envolvidos == null){
            $dividas_correlacionadas = null;
        } else if($correlacionados_cadastro != null && $correlacionados_envolvidos == null){
            $dividas_correlacionadas = $correlacionados_cadastro;
        } else if($correlacionados_cadastro == null && $correlacionados_envolvidos != null){
            $dividas_correlacionadas = $correlacionados_envolvidos;
        } else if($correlacionados_cadastro != null && $correlacionados_envolvidos != null){

            foreach ($correlacionados_cadastro as $index => $cadastro) {
                $dividas_correlacionadas[] = $cadastro;
                $dividas_correlacionadas[] = $correlacionados_envolvidos[$index];
            }

        }  

        $solicitacao = strval($solicitacao);
        $documento = strval($documento);

        date_default_timezone_set('America/Sao_Paulo');
        $meses_em_portugues = array(
            'January' => 'janeiro',
            'February' => 'fevereiro',
            'March' => 'março',
            'April' => 'abril',
            'May' => 'maio',
            'June' => 'junho',
            'July' => 'julho',
            'August' => 'agosto',
            'September' => 'setembro',
            'October' => 'outubro',
            'November' => 'novembro',
            'December' => 'dezembro'
        );
        $data_escrita = date('d \d\e ') . $meses_em_portugues[date('F')] . date(' \d\e Y');        

        $data_atual = date('d/m/Y');
        $hora_atual = date('H:i');
        $hora_atual = date('H\hi', strtotime($hora_atual));
        $tipo_certidao = 'Certidão Positiva';

        $assinaturaResponsavelTable = TableRegistry::getTableLocator()->get('AssinaturaResponsavel', ['connection' => $this->conexaoPortalContribuinte]);
        $dados_procurador = $assinaturaResponsavelTable->find()->select(['nome_responsavel_assinatura','cargo_responsavel_assinatura'])->first();

        $documento = preg_replace('/[^0-9]/', '', $documento);

        if (strlen($documento) < 14) {
            $documento = str_pad($documento, 14, "0", STR_PAD_LEFT);
        }

        $cnpj_formatado = substr_replace($documento, '.', 2, 0);
        $cnpj_formatado = substr_replace($cnpj_formatado, '.', 6, 0);
        $cnpj_formatado = substr_replace($cnpj_formatado, '/', 10, 0);
        $cnpj_formatado = substr_replace($cnpj_formatado, '-', 15, 0);


        $link_qr = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'link'
            ])
            ->from('link_parametro')
            ->where([
                'id_link_tipo' => 3,
                'id_ambiente' => 1,
            ])
            ->execute()
            ->fetch('assoc');

        $linkQrCode = $link_qr['link'];

        
        $link_info = $this->conexaoPortalContribuinte->newQuery()
        ->select([
            'link'
        ])
        ->from('link_parametro')
        ->where([
            'id_link_tipo' => 2,
            'id_ambiente' => 1,
        ])
        ->execute()
        ->fetch('assoc');

        $linkInfo = $link_info['link'];

        $content = '
        <h1 style="text-align: center; font-size: 14px;">CERTIDÃO POSITIVA DE DÉBITOS EM DÍVIDA ATIVA</h1>
        

        <p style="text-align: justify;">Certifico, tendo em vista as informações fornecidas pelo Sistema da Dívida Ativa,  que no período de 1977 até data_atual, conforme solicitado por 
        <b>nome_contribuinte</b>, CNPJ <b>nº documento_contribuinte</b>, <b>CONSTA(M) qtd_dividas DÉBITO(S)</b>, relacionado(s) à requerente, para empresas com mesmo
        Nome, CNPJ ou raiz de CNPJ, corporificado(s) nas incrições listadas no relatório de pesquisa cadastral em anexo, extraído do Sistema da Divida Ativa.</p>
        
        <p style="text-align: justify;">Foram realizadas também pesquisas com base nas informações de incorporações cadastradas no Sistema de Divida Ativa, onde foram localizados <b>qtd_correlacionadas DÉBITO(S)</b>, corporificado(s) nas inscrições listadas no relatório de pesquisa cadastral anexo.</p>

        <p style="text-align: justify;">Foram localizados também para o CNPJ pesquisado, a corresponsabilidade em <b>qtd_envolvido DÉBITO(S) </b>, corporificado(s) nas inscrições listadas no relatório de pesquisa
        cadastral em anexo, extraído do Sistema da Dívida Ativa.      
        
        <p style="text-align: justify;">A presente certidão, lavrada em 01 (uma) lauda e [numero_laudas] lauda(s) de anexo, todas com informações somente no anverso,
        tem validade até o dia data_final_validade considerando o prazo de validade_certidao dias após sua emissão, conforme artigo 11 da resolução nº 2690 de 05/10/2009.</p>

        <p><b>CÓDIGO CERTIDÃO:</b> cod_autenticidade_certidao
        <p><b>PESQUISA CADASTRAL</b> realizada em: data_atual às hora_atualmin</p>
              
        <p style="text-align: justify;">A aceitação desta certidão está condicionada a verificação de sua autenticidade na INTERNET, no endereço: <a href="linkQrCode">linkQrCode</a></p>
            
        <p >Para maiores informações: <a href="linkInfo">linkInfo</a></p>
        
        <br><br>

        <p style="font-size:10px; text-align:center;">Rio de Janeiro, data_escrita.<br>
        nome_procurador<br>
        cargo_procurador PG-06</p>

        <p style="font-size:8px; text-align:center;">Emitida em data_atual às hora_atualmin</p>

        ';
        
        //SUBSTITUIR POR PARAMETROS DO CONTRIBUINTE
        $content = str_replace('nome_contribuinte', $nome, $content);
        $content = str_replace('documento_contribuinte', $cnpj_formatado, $content);
        $content = str_replace('id_solicitacao', $solicitacao, $content);
        $content = str_replace('data_atual', $data_atual, $content);
        $content = str_replace('hora_atual', $hora_atual, $content);
        $content = str_replace('cod_autenticidade_certidao', $cod_autenticidade_certidao, $content);
        $content = str_replace('validade_certidao', strval($dias_validade), $content);
        $content = str_replace('data_final_validade', $validade_certidao, $content);
        $content = str_replace('qtd_dividas', $qtd_dividas, $content);
        $content = str_replace('qtd_envolvido', $qtd_envolvido, $content);
        $content = str_replace('nome_procurador', $dados_procurador['nome_responsavel_assinatura'], $content);
        $content = str_replace('cargo_procurador', $dados_procurador['cargo_responsavel_assinatura'], $content);
        $content = str_replace('data_escrita', $data_escrita, $content);
        $content = str_replace('qtd_correlacionadas', $qtd_correlacionadas, $content);
        $content = str_replace('linkQrCode', $linkQrCode, $content);
        $content = str_replace('linkInfo', $linkInfo, $content);

        $arquivo = $this->Pdf->gerarPdfCertidaoPositivaPj($content,$cod_autenticidade_certidao,$dividas,$envolvidos,$dividas_correlacionadas,$data_atual,$hora_atual);

        //gera o nome de arquivo aleatório e especifico o caminho
        $nomeArquivoTemporario = 'temporario_' . uniqid() . '.pdf';
        $caminhoDestino = WWW_ROOT . 'pdf/' . $nomeArquivoTemporario;

        // Grava PDF em diretório para a função de envio de email ler (este arquivo é excluido após o envio)
        file_put_contents($caminhoDestino, $arquivo);

        //enviar arquivo por email
        $this->enviarCertidaoEmail($destinatario, $cod_autenticidade_certidao, $nome, $tipo_certidao,$caminhoDestino,$solicitacao);
        
        // Deletando arquivo após envio do email.    
        unlink($caminhoDestino);

        return $arquivo;

    }


    public function apiImprimeCertidao(){

        $request = $this->getRequest();
        $id_solicitacao = intval($request->getQuery('id_solicitacao'));

        //DEBUGAR API
        // $file = WWW_ROOT . 'img/debug.txt';
        // $debugContent = 'debug: ' . $request->getQuery('id_solicitacao') . PHP_EOL;
        // file_put_contents($file, $debugContent); 

//        $id_solicitacao = 25;

        $this->viewBuilder()->setLayout('pdf');
    
        $certidaoTable = TableRegistry::getTableLocator()->get('Certidao', ['connection' => $this->conexaoPortalContribuinte]);
        $pdfData = $certidaoTable->find()
            ->select(['certidao_arquivo'])
            ->where(['id_solicitacao' => $id_solicitacao])
            ->first();
    
        if ($pdfData !== null) {
            $this->response = $this->response->withType('application/pdf');
            $this->response = $this->response->withStringBody(stream_get_contents($pdfData->certidao_arquivo));
            return $this->response;
        } else {
            $this->Flash->error('Arquivo PDF não encontrado.');
            return $this->redirect(['action' => 'index']);
        }
    }


    public function enviarCertidaoEmail($destinatario, $cod_autenticidade_certidao, $nome, $tipo_certidao,$caminhoDestino,$id_solicitacao){

        $data = date('d/m/Y');
        
        // Leitura do conteúdo do arquivo PDF
        $pdfContent = file_get_contents($caminhoDestino);
        
        // Instância do Mailer
        $mailer = new Mailer('default');

        // Configuração do remetente
        $mailer->setFrom(['certidao.pda@pge.rj.gov.br' => 'Procuradoria Geral do Estado do RJ - Divida Ativa']);
        
        // Configuração do e-mail
        $mailer->setTo($destinatario)
            ->setSubject('Portal do Contribuinte - Certidão de Regularidade Fiscal')
            ->setEmailFormat('html')
            ->setAttachments([
                'CRF'.$cod_autenticidade_certidao.'.pdf' => [
                    'data' => $pdfContent,
                    'mimetype' => 'application/pdf'
                ]
            ])
            ->setViewVars([
                'titulo' => 'Solicitação de Certidão de Regularidade Fiscal',
                'nome' => $nome,
                'tipo_certidao' => $tipo_certidao,
                'id_solicitacao' => $id_solicitacao,
                'data' => $data,
            ])
            ->viewBuilder()
            ->setTemplate('email_certidao') // Define o template de e-mail criado
            ->setLayout('default') // Substitua 'default' pelo nome do layout que você criou (opcional)
            ->setTemplatePath('Email/html/'); // Especifica o diretório do template
            
        $mailer->send();

    }

    
}


