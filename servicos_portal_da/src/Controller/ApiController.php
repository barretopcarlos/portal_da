<?php
declare(strict_types=1); 

namespace App\Controller;

use Cake\Database\Expression\QueryExpression;
use Exception;
use InvalidArgumentException;


/**
 * ApiController Controller
 *
 * @method \App\Model\Entity\ApiController[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */

class ApiController extends AppController{

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
    
    const CONNECTION_ERROR_LIMIT = '5';

    public function initialize() : void {
        
        parent::initialize();

    }


    public function consultarDebitosFiscais() {

        // $parametros = [
        //     'campo' => 'Certidão',
        //     'valor' => '2016/005.585-9',
        //     'g-recaptcha-response' => '03AL8dmw8TFou-xwyf6aGJVSp3ihU-A3XFb-mEBcdZZn-sJ-eEOyLM98SSEewm8Vejqlf3KEBl59jGnwPReiQmy8qKAAYr3kh_TrlwKGWdD3AZj9eMo5O35y-HSfGCwrd0dG-a9P1HYXiTfrVztiRAbmkAJ9ZVU10seApF6hls40vO50FQOmmrqa2co98q2nasKWGq7YF2faTzZOWs3B0yM_6-0tC9bC3-miQHBdkEoyPjskxV6Rp7m1f4X9ahSRTytZZlncsLOmCSGw04qE2U3AiglgXSjvPkpJmw0E1LvhiDDkejOdB_1gxy93lfjBFFpVBOzsFApZvwq2DFGxuNfCb5LV2Zeso5uhf3lckIg3lKXfE8j2Dy4fNpb8X7N-Lkvf6N7zs1ikO7btBv7sAuZ7FhLjCFiTytEpPYuHiQcWQnlVTZwYHuK5lTNcaATZ8uzoRdAfTDhTv7AAgj5qVupfBcwfx5AfZKoSz1pbdkDiuIiXqQ1L6tuWh9_29Z-SDDqopbFI-Ty64Hyo2HQsn_3vle8PY7nBu35PFVivCz4-YBuQD-HEbS_aFaun9zKnGXQGvu-XsYocSD',
        //     'campo_pa' => 'E-',
        //     'valor_pa_orgao' => '',
        //     'valor_pa_unidade_protocoladora' => '',
        //     'valor_pa_processo' => '',
        //     'valor_pa_ano' => '',
        //     'raizCnpj' => '0',
        // ];

        // $parametros = [
        //     'campo' => 'Processo Judicial novo',
        //     'valor' => '0013929-70.1978.8.19.0001',
        //     'g-recaptcha-response' => '03AL8dmw-XMBjXb__rfWyq5QipkhZDLX6qgkXaiofNG-CZBCGCV6tiHcjpDAazY1qwL6l13Y9bDtOZpd5ntP5u5L4PWP0gokIkDlHalQ_m7i-l5DEfGu4x43PfL4n6awfWxxqqfQhwMBfcRJHP57yxARs9T5hnu4SO87e91WILfYRdielHDlE5UOqwWRiwRlM3hqrE4dfljJGghirqdN2M-bMkPf128zio405670_i7mtUL7_TX21EpcYx63rxL_YqXieT3VJTqVH1kpbneqd8CwagDgnf1Nn5_AmFuI7ObebYQoCgnv1xU1djhPJyNtXo8x82e_hAXFgT2H5nQwW0m8ALPhUSG-pvDfmCrfrTwTjFE9uhEcy_7pA_sWBMFZXd_KUqF19KjNKqlib3nLBj7Uqoll2UOxv3xhZwru0pdcPkWbYM6O3cfA_flg0oKXT5kZIZJZHWUpLOep4KgnVdL6xk44ExNgOOhsifGm_Vuk7eWpLx9kX9n6H4lYXLAvq1PVr06C6Zrz06OgRC9pNvrVFESlEqUJ38-C3IXz8cvkcHWhsNl9TcTJWnOV7VceWkNxKyJQwN1MJ9',
        //     'campo_pa' => 'E-',
        //     'valor_pa_orgao' => '',
        //     'valor_pa_unidade_protocoladora' => '',
        //     'valor_pa_processo' => '',
        //     'valor_pa_ano' => '',
        //     'raizCnpj' => '0',
        // ];


        try{
        
            $parametros = $this->request->getQuery();

            if (empty($parametros)) {
                throw new InvalidArgumentException("Nenhum parâmetro foi enviado. Não foi possível realizar a requisição.");
            }

           

            if ($parametros['campo'] !== null) {
                $campoEscolhido = $parametros['campo'];
            }else{
                throw new InvalidArgumentException("Não foi possível realizar a requisição.");
            }

            $campo = $this->opcoes[$campoEscolhido];
            $coluna = $this->getColuna($campo);

            if ($parametros['valor'] !== null) {

                $valor = preg_replace('/[^0-9]/', '', $parametros['valor']);

                //retirar zeros a esquerda, exceto cnj novo e antigo
                if($campo != 'Processo Judicial novo'){
                    $valor = ltrim($valor, '0');
                }else if ($campo != 'Processo Judicial novo'){
                    $valor = ltrim($valor, '0');
                }              

            }else{
                throw new InvalidArgumentException("Não foi possível realizar a requisição.");
            }


            if($campo == 'CNPJ'){

                if ($parametros['raizCnpj'] !== null) {
                    $testeRaiz = $parametros['raizCnpj'];
                    $cadastros = $this->filtrarRaizCnpj($valor,$testeRaiz);
                    $cadastros = $cadastros->toArray();
                }else{
                    throw new InvalidArgumentException("Não foi possível realizar a requisição.");
                }

            }else if($campo == 'Taxa de Incêndio'){

                if ($parametros['cidade_logradouro'] !== null) {
                    $municipio = $parametros['cidade_logradouro'];
                    $cadastros = $this->filtrarTaxaIncendio($valor,$municipio);
                }else{
                    throw new InvalidArgumentException("Não foi possível realizar a requisição.");
                }

            }else if($campo == 'Processo Administrativo'){

                if ($parametros['campo_pa'] !== null) {
                    $campoEscolhidoPa = $parametros['campo_pa'];
                }else{
                    throw new InvalidArgumentException("Não foi possível realizar a requisição.");
                }               

                //formatando saida dos numeros dos PAs
                $valor_orgao = $parametros['valor_pa_orgao'];
                if ($campoEscolhidoPa == 'E-') {
                    $valor_orgao = str_pad($valor_orgao, 2, '0', STR_PAD_LEFT);
                }

                $valor_unidade_protocoladora = $parametros['valor_pa_unidade_protocoladora'];
                if ($campoEscolhidoPa == 'E-') {
                    $valor_unidade_protocoladora = str_pad($valor_unidade_protocoladora, 3, '0', STR_PAD_LEFT);
                }else if($campoEscolhidoPa == 'SEI-'){
                    $valor_unidade_protocoladora = str_pad($valor_unidade_protocoladora, 6, '0', STR_PAD_LEFT);
                }

                $valor_processo = $parametros['valor_pa_processo'];
                if ($campoEscolhidoPa == 'E-' || $campoEscolhidoPa == 'SEI-' || $campoEscolhidoPa == 'IPS') {
                    $valor_processo = str_pad($valor_processo, 6, '0', STR_PAD_LEFT);
                }else if ($campoEscolhidoPa == 'E-66' || $campoEscolhidoPa == 'E-77' || $campoEscolhidoPa == 'E-88' || $campoEscolhidoPa == 'E-99'){
                    $valor_processo = str_pad($valor_processo, 11, '0', STR_PAD_LEFT);
                }

                $valor_ano = $parametros['valor_pa_ano'];

                $processo_adm_banco = $this->convertePa($campoEscolhidoPa,$valor_orgao,$valor_unidade_protocoladora,$valor_processo,$valor_ano);

                if($processo_adm_banco === null){
                    $cadastros = [];
                }else{
                    $cadastros = $this->filtrarPA($processo_adm_banco);
                }

                $cadastros = $cadastros->toArray();
                
            }else{

                if($campo == 'CPF'){
                    $coluna = 'numero_documento_principal';
                }

                $cadastros = $cadastros = $this->getTableLocator()
                    ->get('Cadastro')
                    ->find()
                    ->select(['numero_documento_principal',
                    'linha' => 'ROW_NUMBER() OVER (ORDER BY id ASC)',
                    'numero_cda',
                    'numero_documento_principal',
                    'auto_infracao',
                    'codigo_natureza',
                    'data_inscricao',
                    'processo_administrativo',
                    'auto_infracao',
                    'cidade_logradouro',
                    'valor_total_moeda',
                    'nome_devedor',
                    'Natureza.nome',
                    'data_ultimo_calculo',
                    'codigo_movimento',
                    'data_cancelamento',
                    'data_ajuizamento',
                    'codigo_localidade',
                    'Localidade.municipio'])
                    ->where(function (QueryExpression $exp) use ($coluna, $valor) {
                        return $exp->like($coluna, $valor)
                        ->isNull('data_cancelamento');
                    })
                    ->innerJoinWith('Natureza')
                    ->innerJoinWith('Localidade')
                    ->contain([
                        'Natureza' => [
                            'fields' => [
                                'codigo',
                                'nome',
                            ],
                            'strategy' => 'select'
                        ],
                        'Localidade' => [
                            'fields' => [
                                'codigo_municipio_serventia',
                                'municipio'
                            ],
                            'strategy' => 'select'
                        ]
                    ])
                    ->offset($this->request->getQuery('offset', 0));

                    $cadastros = $cadastros->toArray();

            }

            if (empty($cadastros)) {
                throw new InvalidArgumentException("Não foi possível executar esta requisição.");
            }

            //Resposta da API
            $resultado = [
                'data' => $cadastros
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
    

    public function getColuna(string $campo): ? string{

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

    public function filtrarTaxaIncendio($inscricao_predial,$municipio) {
        

        $cadastros = $this->getTableLocator()
        ->get('Cadastro')
        ->find()
        ->select([
                'linha' => 'ROW_NUMBER() OVER (ORDER BY id ASC)',
                'numero_documento_principal',
                'numero_cda',
                'numero_documento_principal',
                'auto_infracao',
                'codigo_natureza',
                'data_inscricao',
                'processo_administrativo',
                'auto_infracao',
                'cidade_logradouro',
                'valor_total_moeda',
                'nome_devedor',
                'Natureza.nome',
                'data_ultimo_calculo',
                'codigo_movimento',
                'data_cancelamento',
                'data_ajuizamento',
                'codigo_localidade',
                'Localidade.municipio'])
        
        ->where(function (QueryExpression $exp) use ($inscricao_predial, $municipio) {
            return $exp->eq('inscricao_predial', $inscricao_predial)
                ->eq('Localidade.municipio', $municipio)
                ->isNull('data_cancelamento');
        })
        ->innerJoinWith('Natureza')
            ->innerJoinWith('Localidade')
            ->contain([
                'Natureza' => [
                    'fields' => [
                        'codigo',
                        'nome',
                    ],
                    'strategy' => 'select'
                ],
                'Localidade' => [
                    'fields' => [
                        'codigo_municipio_serventia',
                        'municipio'
                    ],
                    'strategy' => 'select'
                ]
                ]);
        

        return $cadastros;

    }


    public function filtrarRaizCnpj($numero,$tipoFiltro) {

        $numero = ltrim($numero, '0');

        $proc_adm = $this->getTableLocator()
        ->get('Cadastro')
        ->find()
        ->select(['numero_documento_principal'])
        ->where(['numero_documento_principal' => $numero])
        ->distinct(['numero_documento_principal']);

        if ($proc_adm) {
            
            $numero_raiz = substr($numero, 0, 8); 
           
            if ($tipoFiltro == '0') {

                $cadastros = $this->getTableLocator()->get('Cadastro')
                    ->find()
                    ->select(['numero_documento_principal',
                'linha' => 'ROW_NUMBER() OVER (ORDER BY id ASC)',
                'numero_cda',
                'numero_documento_principal',
                'auto_infracao',
                'codigo_natureza',
                'data_inscricao',
                'processo_administrativo',
                'auto_infracao',
                'cidade_logradouro',
                'valor_total_moeda',
                'nome_devedor',
                'Natureza.nome',
                'data_ultimo_calculo',
                'codigo_movimento',
                'data_cancelamento',
                'data_ajuizamento',
                'codigo_localidade',
                'Localidade.municipio'])

                    ->where(function ($exp, $q) use ($numero) {
                        return $exp->like('numero_documento_principal', $numero)
                        ->isNull('data_cancelamento')
                        ;
                    })
                    ->innerJoinWith('Natureza')
                    ->innerJoinWith('Localidade')
                    ->contain([
                        'Natureza' => [
                            'fields' => [
                                'codigo',
                                'nome',
                            ],
                            'strategy' => 'select'
                        ],
                        'Localidade' => [
                            'fields' => [
                                'codigo_municipio_serventia',
                                'municipio'
                            ],
                            'strategy' => 'select'
                        ]
                    ])
                    ->offset($this->request->getQuery('offset', 0));
                

            } else {
                $cadastros = $this->getTableLocator()
                    ->get('Cadastro')
                    ->find()
                    ->select(['numero_documento_principal',
                    'linha' => 'ROW_NUMBER() OVER (ORDER BY id ASC)',
                    'numero_cda',
                    'numero_documento_principal',
                    'auto_infracao',
                    'codigo_natureza',
                    'data_inscricao',
                    'processo_administrativo',
                    'auto_infracao',
                    'cidade_logradouro',
                    'valor_total_moeda',
                    'nome_devedor',
                    'Natureza.nome',
                    'data_ultimo_calculo',
                    'codigo_movimento',
                    'data_cancelamento',
                    'data_ajuizamento',
                    'codigo_localidade',
                    'Localidade.municipio'])
                    ->where(function ($exp, $q) use ($numero_raiz) {
                        return $exp->eq('LEFT(numero_documento_principal, 8)', substr($numero_raiz, 0, 8))
                        ->isNull('data_cancelamento');
                    })
                    ->innerJoinWith('Natureza')
                    ->innerJoinWith('Localidade')
                    ->contain([
                        'Natureza' => [
                            'fields' => [
                                'codigo',
                                'nome',
                            ],
                            'strategy' => 'select'
                        ],
                        'Localidade' => [
                            'fields' => [
                                'codigo_municipio_serventia',
                                'municipio'
                            ],
                            'strategy' => 'select'
                        ]
                    ])
                    ->offset($this->request->getQuery('offset', 0));
                    //->distinct(['numero_documento_principal'])
                
            }

            return $cadastros;

        } else {
            return [];
        }
    }


    public function convertePa(string $campo, $orgao, $unidade, $processo, $ano): ?string{

        if ($campo == "E-") {

            $numero = sprintf("1%02d0%03d0%06d%04d", $orgao, $unidade, $processo, $ano);

        } elseif ($campo == "SEI-") {

            $unidade = str_pad($unidade, 6, '0', STR_PAD_LEFT);
            $processo = str_pad($processo, 6, '0', STR_PAD_LEFT);
            $numero = sprintf("2%s%d%s%d", $unidade,"0", $processo, $ano);
        

        } elseif ($campo == "E-66" || $campo == "E-77" || $campo == "E-88" || $campo == "E-99") {


            $processo = str_pad($processo, 11, '0', STR_PAD_LEFT);

            if($campo == "E-66"){
                $numero = sprintf("8%02d%s%d", '66', $processo, $ano);
            }elseif($campo == "E-77"){
                $numero = sprintf("8%02d%s%d", '77', $processo, $ano);
            }else if($campo == "E-88"){
                $numero = sprintf("8%02d%s%d", '88', $processo, $ano);
            }else if($campo == "E-99"){
                $numero = sprintf("8%02d%s%d", '99', $processo, $ano);
            }

        } elseif ($campo == "IPS") {

            $processo = str_pad($processo, 6, '0', STR_PAD_LEFT);
            $numero = sprintf("9%07d%06d%04d", 0, $processo, $ano);

        } else {
            
            $numero = "";
        }

        return $numero;
    }


    public function filtrarPa($processo_adm_banco){

        $cadastros = $this->getTableLocator()
            ->get('Cadastro')
            ->find()
            ->select(['numero_documento_principal',
                'linha' => 'ROW_NUMBER() OVER (ORDER BY id ASC)',
                'numero_cda',
                'numero_documento_principal',
                'auto_infracao',
                'codigo_natureza',
                'data_inscricao',
                'processo_administrativo',
                'auto_infracao',
                'cidade_logradouro',
                'valor_total_moeda',
                'nome_devedor',
                'Natureza.nome',
                'data_ultimo_calculo',
                'codigo_movimento',
                'data_cancelamento',
                'data_ajuizamento',
                'codigo_localidade',
                'Localidade.municipio'])
            ->where(function ($exp) use ($processo_adm_banco) {
                return $exp->like('processo_administrativo', $processo_adm_banco)
                ->isNull('data_cancelamento');
            })
            ->innerJoinWith('Natureza')
            ->innerJoinWith('Localidade')
            ->contain([
                'Natureza' => [
                    'fields' => [
                        'codigo',
                        'nome',
                    ],
                    'strategy' => 'select'
                ],
                'Localidade' => [
                    'fields' => [
                        'codigo_municipio_serventia',
                        'municipio'
                    ],
                    'strategy' => 'select'
                ]
                ])
            ->offset($this->request->getQuery('offset', 0));
            
        return $cadastros;       
       
    }


    public function listarMunicipios(){

        $localidade = $this->getTableLocator()->get('Localidade');
        $query = $localidade->find();
        $query->select(['municipio'])
            ->matching('Cadastro', function ($q) {
                return $q->where(['Localidade.codigo_municipio_serventia = Cadastro.codigo_localidade']);
            })
            ->where(function ($exp, $q) {
                return $exp->isNotNull('municipio');
            })
            ->group('municipio')
            ->order(['municipio' => 'ASC']);
        $municipios = $query->toArray();

        $response = $this->response->withType('application/json')->withStringBody(json_encode(['municipios' => $municipios]));

        return $response;

    }


    
}

