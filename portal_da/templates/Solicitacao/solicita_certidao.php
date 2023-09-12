<?php

echo $this->Html->script('jquery-3.6.4.min');
echo $this->Html->script('jquery.inputmask.min');
echo $this->Html->script('bootstrap.min');
echo $this->Html->script('index_solicitacao');
echo $this->Html->css(['solicitacao/index','solicitacao/solicitar-certidao']);

$data_hora = $arrayResultado['solicitacao']['data_hora_solicitacao'];
$data = date('d/m/Y', strtotime($data_hora));
$certidao = $arrayResultado['solicitacao']['id_certidao_tipo'];
$solicitacao = strval($arrayResultado['solicitacao']['id']);
$tipo_contribuinte = $arrayResultado['solicitacao']['id_contribuinte_tipo'];

$conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#conteudo-principal', ['class' => 'skip-main']);
echo $conteudo_principal;

// Exemplo BreadCrumb - Alterar quando tiver home do site
echo $this->Html->tag('div',
    $this->Html->tag('span', 'Home', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']).
    $this->Html->tag('span', 'Portal do contribuinte', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']) .
    $this->Html->tag('span', 'Solicitação', ['class' => 'span-bb']), ['class' => 'exemplo_breadcrumb no-print']);

//Titulo
echo $this->Html->tag('h1', 'Solicitação de Certidões de Regularidade Fiscal', ['title' => 'Solicitação de Certidões de Regularidade Fiscal','aria-label'=>'Solicitação de Certidões de Regularidade Fiscal']).
$this->Html->tag('br'),
$this->Html->tag('h2', 'Utilize esta funcionalidade para a solicitar suas certidões de regularidade fiscal.', ['title' => 'Utilize esta funcionalidade para a solicitar suas certidões de regularidade fiscal.']);


if(isset($arrayResultado['existente']) && $arrayResultado['existente'] == true){

    echo $this->Html->tag('div',

        $this->Html->tag('div',
            $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
            $this->Html->tag('p', 'Existe uma solicitação processada na data de hoje, não é possível realizar mais de uma solicitação no mesmo dia.', ['class' => 'texto-solicitacao-aprovada'])
        , ['class' => 'info-impressao'])

    , ['class' => 'col-sm-8']);

}else{

    echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'check_circle', ['class' => 'material-icons icon-check_circle']).
        $this->Html->tag('p', 'Solicitação aprovada por análise eletrônica e disponibilizada no e-mail informado.', ['class' => 'texto-solicitacao-aprovada'])
    , ['class' => 'solicitacao-aprovada'])

, ['class' => 'col-sm-6']);

}


if($tipo_contribuinte == 1){

    $documento = preg_replace('/[^0-9]/', '', $parametros['valor']);
    $documento_formatado = substr_replace($documento, '.', 3, 0);
    $documento_formatado = substr_replace($documento_formatado, '.', 7, 0);
    $documento_formatado = substr_replace($documento_formatado, '-', 11, 0);
    $tipo_contribuinte = "Pessoa Física";

}else if($tipo_contribuinte == 2){

    $documento = preg_replace('/[^0-9]/', '', $parametros['valor']);
    $documento_formatado = substr_replace($documento, '.', 2, 0);
    $documento_formatado = substr_replace($documento_formatado, '.', 6, 0);
    $documento_formatado = substr_replace($documento_formatado, '/', 10, 0);
    $documento_formatado = substr_replace($documento_formatado, '-', 15, 0);
    $tipo_contribuinte = "Pessoa Jurídica";

}


if($certidao == 3 || $certidao == 4){
    $certidao = "Certidão Negativa de Débitos";
}else if($certidao == 1 || $certidao == 2){
    $certidao = "Certidão Positiva de Débitos";
}


echo $this->Html->tag('div',

    $this->Html->tag('p', '<b>Tipo de Certidão: '. $certidao.'</b>', ['class' => 'label-pedido']).  
    $this->Html->tag('p', '<b>Tipo de Contribuinte: </b>'. $tipo_contribuinte, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Contribuinte: </b>'. $parametros['nome_contribuinte'], ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Documento do Contribuinte: </b>'. $documento_formatado, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>E-mail: </b>'. $parametros['email'], ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Nº da solicitação: </b>'. $solicitacao, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Data da Solicitação: </b>'. $data, ['class' => 'label-pedido'])

, ['class' => 'dados-pedido']).


$this->Html->tag('div',
    $this->Html->tag('p', '<b>Sua certidão está disponível! Clique no botão abaixo para realizar a impressão. </b>', ['class' => 'texto-disponivel'])
, ['class' => 'texto-disponível']).


$this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
        $this->Html->tag('p', 'Para imprimir é necessário desabilitar pop-ups no seu navegador.', ['class' => 'texto-solicitacao-aprovada'])
    , ['class' => 'info-impressao'])

, ['class' => 'col-sm-6']);

// Botões do formulário
if(isset($arrayResultado['existente']) && $arrayResultado['existente'] == true){
    $textoBotao = 'Reimprimir Certidão';
}else{
    $textoBotao = 'Imprimir Certidão';
}

echo $this->Html->tag('div',

    $this->Html->tag('span',
    $this->Html->link('Voltar', ['controller' => 'Solicitacao', 'action' => 'index'], [
        'class' => 'bt-grupo bt-solicitacao-voltar',
        'title' => 'Voltar'
    ]),    ['class' => 'bts']).

    $this->Html->tag('button',
    '<i class="material-icons icon-print">local_printshop</i>'. $textoBotao,
    [
        'type' => 'button',
        'class' => 'bt-solicitacao-grupo bt-imprimir-certidao',
        'title' => 'Imprimir Certidão',
        'id' => 'btn-imprimir-certidao ',
        'onclick' => 'window.open("'
                . $this->Url->build(['controller' => 'Solicitacao', 'action' => 'imprimirCertidao', $solicitacao])
                . '", "_blank")'
    ]
)
    
, ['class' => 'bt-group col-sm-4', 'id'=>'conteudo-principal']);


if($certidao == 'Certidão Positiva de Débitos'){

    echo $this->Html->tag('div',
        $this->Html->tag('div',
            $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
            $this->Html->tag('p', 'ATENÇÃO, constam dívidas em seu nome.', ['class' => 'texto-atencao'])
        , ['class' => 'info-atencao'])
    , ['class' => 'col-sm-6 atencao']);

    
    echo $this->Html->tag('h2', 'Caso deseje efetuar o pagamento de sua dívida.', ['title' => 'Emitir DARJ.','class'=>'texto-darj']);


    echo $this->Html->tag('button',
    'Emissão DARJ',
    [
        'type' => 'button',
        'class' => 'bt-solicitacao-grupo bt-imprimir-certidao',
        'title' => 'Emitir DARJ',
        'id' => 'btn-darj',
        'onclick' => "window.open('http://www.consultadividaativa.rj.gov.br/RDGWEBLNX/servlet/StartCISPage?PAGEURL=/cisnatural/NatLogon.html&xciParameters.natsession=Emissao_DARJ_DA', '_blank');",
    ]);

    echo $this->Html->tag('h2', 'Caso deseje emitir uma Certidão Positiva com Efeito de Negativa.', ['title' => 'Emitir CPEN.','class'=>'texto-cpen']);

    echo $this->Html->tag('button',
    'Solicitar CPEN',
    [
        'type' => 'button',
        'class' => 'bt-solicitacao-grupo bt-imprimir-certidao',
        'title' => 'Emitir CPEN',
        'id' => 'btn-cpen ',
    ]);

    
}


?>






