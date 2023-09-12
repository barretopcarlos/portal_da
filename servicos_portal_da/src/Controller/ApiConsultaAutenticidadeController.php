<?php
declare(strict_types=1); 

namespace App\Controller;

use Exception;
use InvalidArgumentException;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;

/**
 * ApiConsultaAutenticidadeController Controller
 *
 * @method \App\Model\Entity\ApiConsultaAutenticidadeController[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */

class ApiConsultaAutenticidadeController extends AppController{

   
    const CONNECTION_ERROR_LIMIT = '5';

    public function initialize() : void {
        
        parent::initialize();
        $this->conexaoDefault = ConnectionManager::get('default');
        $this->conexaoPortalContribuinte = ConnectionManager::get('portal_contribuinte');
        $this->loadComponent('Pdf');

    }

    public function consultaAutenticidade() {

        try{
            
            $parametros = $this->request->getQuery();
            $cod_autenticidade = $parametros['valor'];

            //$cod_autenticidade = "8709-2023-AWNJ-25";
            
            $resultado_certidao = $this->conexaoPortalContribuinte->newQuery()
            ->select([
                'certidao.cod_autenticidade_certidao',
                'solicitacao.id as id_solicitacao',
                'certidao.nome_contribuinte',
                'certidao.documento_contribuinte',
                'certidao.data_hora_consulta',
                'certidao.certidao_validade',
                'contribuinte_tipo.nome_contribuinte_tipo',
                'contribuinte_tipo.id as id_contribuinte_tipo',
                'certidao_tipo.nome_certidao_tipo',
                'certidao_tipo.id as id_certidao_tipo'
            ])
            ->from('certidao')
            ->join([
                'table' => 'solicitacao',
                'alias' => 'solicitacao',
                'type' => 'LEFT',
                'conditions' => [
                    'certidao.id_solicitacao = solicitacao.id'
                ]
            ])

            ->join([
                'table' => 'contribuinte_tipo',
                'alias' => 'contribuinte_tipo',
                'type' => 'LEFT',
                'conditions' => [
                    'solicitacao.id_contribuinte_tipo = contribuinte_tipo.id'
                ]
            ])

            ->join([
                'table' => 'certidao_tipo',
                'alias' => 'certidao_tipo',
                'type' => 'LEFT',
                'conditions' => [
                    'solicitacao.id_certidao_tipo = certidao_tipo.id'
                ]
            ])

            ->where(['certidao.cod_autenticidade_certidao' => $cod_autenticidade])
            ->execute()
            ->fetch('assoc');

            $data_validade = $resultado_certidao['certidao_validade']; 

            // Obtendo a data e hora atual e transformando em obj FronzenTime a data da validade da certidao
            $dataAtual = FrozenTime::now();
            $data_validade = new FrozenTime($data_validade);
            $valida = $dataAtual < $data_validade == true ? true : false;

            $validade = [];
               
            $validade = [
                'data_validade' => $data_validade,
                'valida' => $valida
            ];

            //Resposta da API
            $resultado = [
                'certidao' => $resultado_certidao,
                'validade' => $validade
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



    public function imprimirConsultaAutenticidade(){

        $parametros = $this->request->getQuery();

        $data_consulta_autenticidade = $parametros['data_consulta_autenticidade'];
        $codigo_certidao = $parametros['codigo_certidao'];
        $tipo_certidao = $parametros['tipo_certidao'];
        $tipo_contribuinte = $parametros['tipo_contribuinte'];
        $nome_contribuinte = $parametros['nome_contribuinte'];
        $documento_contribuinte = $parametros['documento_contribuinte'];
        $tipo_documento = $parametros['tipo_documento'];
        $data_consulta_certidao = $parametros['data_consulta_certidao'];
        $data_hora_validade_formatada = $parametros['data_hora_validade_formatada'];
        $valida = $parametros['valida'];

        $texto_validade = $valida === '1' ? '<h2 style="font-size: 12px; color:#005A92"><b>Confira os dados abaixo para verificar a autenticidade da certidão consultada.</b></h2>' : '<h2 style="font-size: 12px; color:red"><b>A certidão consultada está fora da validade, por favor, solicite uma nova certidão.</b></h2>';

        $content = '
        <h1 style="text-align: center; font-size: 14px;">Consulta de Autenticidade de Certidão</h1>

        <br>

        texto_validade
        
            
        <p><b>Data e hora da consulta: </b>data_consulta_certidao</p>
        <p><b>Código da Certidão: </b>cod_certidao</p>
        <p><b>Tipo de Certidão: </b>tipo_certidao</p>
        <p><b>Tipo de Contribuinte: </b>tipo_contribuinte</p>
        <p><b>Nome do Contribuinte: </b>nome_contribuinte</p>
        <p><b>Nº do tipo_documento : </b>documento_contribuinte</p>
        <p><b>Data e hora da emissão da certidão: </b>data_certidao</p>
        <p><b>Data da validade da certidão: </b>validade_certidao</p>

        ';

        $content = str_replace('texto_validade', $texto_validade, $content);
        $content = str_replace('data_consulta_certidao', $data_consulta_autenticidade, $content);
        $content = str_replace('cod_certidao', $codigo_certidao, $content);
        $content = str_replace('tipo_certidao', $tipo_certidao, $content);
        $content = str_replace('tipo_contribuinte', $tipo_contribuinte, $content);
        $content = str_replace('nome_contribuinte', $nome_contribuinte, $content);
        $content = str_replace('tipo_documento', $tipo_documento, $content);
        $content = str_replace('documento_contribuinte', $documento_contribuinte, $content);
        $content = str_replace('data_certidao', $data_consulta_certidao, $content);
        $content = str_replace('validade_certidao', $data_hora_validade_formatada, $content);


        $data = date('d/m/Y');
        $hora = date('H:i');
        $hora = date('H\hi', strtotime($hora));

        $pdf = $this->Pdf->gerarPdfConsultaAutenticidade($content,$codigo_certidao,$data_consulta_autenticidade);

        echo $pdf;

    }       

}


