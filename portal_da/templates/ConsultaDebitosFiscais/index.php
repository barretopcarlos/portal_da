<?php 

echo $this->Html->script('jquery-3.6.4.min');
echo $this->Html->script('jquery.inputmask.min');
echo $this->Html->css(['consultaDebitosFiscais/index']);
echo $this->Html->script('index_consulta_debitos');

echo $this->Html->script('https://www.google.com/recaptcha/api.js?hl=pt');

$conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#conteudo-principal', ['class' => 'skip-main']);
echo $conteudo_principal;

echo $this->Flash->render('auth', ['params' => ['class' => 'alert-danger']]);

//MSGS DE ERRO
echo $this->Html->tag('div',
    $this->Html->tag('span','Por favor, selecione uma opção e preencha o número do documento para realizar a consulta.',['class'=>'sel-doc-text']).
    $this->Html->tag('i', 'close', [
        'class' => 'material-icons no-print icon-print icon-close',
    ])
,['class'=>'sel-doc','style'=>'display:none','id'=>'opcao-doc']);

echo $this->Html->tag('div',
    $this->Html->tag('span','Por favor, preencha o número do documento para realizar a consulta..',['class'=>'sel-doc-text']).
    $this->Html->tag('i', 'close', [
        'class' => 'material-icons no-print icon-print icon-close',
    ])
,['class'=>'sel-doc','style'=>'display:none','id'=>'numero-doc']);

echo $this->Html->tag('div',
    $this->Html->tag('span','Por favor, selecione um tipo de documento para realizar a consulta.',['class'=>'sel-doc-text']).
    $this->Html->tag('i', 'close', [
        'class' => 'material-icons no-print icon-print icon-close',
    ])
,['class'=>'sel-doc','style'=>'display:none','id'=>'tipo-doc']);

echo $this->Html->tag('div',
    $this->Html->tag('span','Por favor, selecione um municipio.',['class'=>'sel-doc-text']).
    $this->Html->tag('i', 'close', [
        'class' => 'material-icons no-print icon-print icon-close',
    ])
,['class'=>'sel-doc','style'=>'display:none','id'=>'sel-municipio']);

echo $this->Html->tag('div',
    $this->Html->tag('span','Por favor, Preencha todos os campos do Processo.',['class'=>'sel-doc-text']).
    $this->Html->tag('i', 'close', [
        'class' => 'material-icons no-print icon-print icon-close',
    ])
,['class'=>'sel-doc','style'=>'display:none','id'=>'numero-proc-adm']);

echo $this->Html->tag('div',
    $this->Html->tag('span','Por favor, marque o Captcha.',['class'=>'sel-doc-text']).
    $this->Html->tag('i', 'close', [
        'class' => 'material-icons no-print icon-print icon-close',
    ])
,['class'=>'sel-doc','style'=>'display:none','id'=>'sel-cap']);


echo $this->Html->scriptBlock('
    $(document).ready(function() {
        $(".icon-close").click(function() {
            $(".sel-doc").hide();
        });
    });
');


// Exemplo BreadCrumb - Alterar quando tiver home do site
echo $this->Html->tag('div',
    $this->Html->tag('span', 'Home', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']).
    $this->Html->tag('span', 'Portal do contribuinte', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']) .
    $this->Html->tag('span', 'Consulta Valores de débitos', ['class' => 'span-bb']), ['class' => 'exemplo_breadcrumb no-print']);

//Titulo
echo $this->Html->tag('h1', 'Consulta de valores de débitos', ['title' => 'Consulta de Valores de débitos','aria-label'=>'Consulta de valores de débitos']).
$this->Html->tag('br'),
$this->Html->tag('h2', 'Consulte os valores de débitos inscritos em Dívida Ativa por uma das opções disponíveis.', ['title' => 'Consulte os valores de débitos inscritos em Dívida Ativa por uma das opções disponíveis.']);


echo $this->Html->tag('div',

    $this->Form->create(null, [
        'url' => ['action' => 'consultarDebitosFiscais'], 
        'type' => 'get', 
        'id' => 'form_consulta', 
        'onsubmit' => 'event.preventDefault(); onSubmit();'
    ]).

    //CAMPO
    $this->Html->tag('div',
        $this->Form->label('label_campo', 'Consulta Por',['class'=>"label-cdf col-md-4 col-form-label",'title'=>'Consulta Por']).
        $this->Html->tag('div',
            $this->Form->control('campo', ['class'=>" form-control",'id'=>'campo','label' => false,'options' => $opcoes,"title"=>"Escolha uma opção de documento",'style'=>'padding-left:10px !important;']),
        ['class'=>'col-md-8']),
    ['class'=>'form-group row group-campo','id'=>'conteudo-principal']).


    //VALOR
    $this->Html->tag('div',
        $this->Form->label('valor', '',['class'=>"label-cdf col-md-4 col-form-label",'id'=>'label_valor']).
        $this->Html->tag('div',
            $this->Form->text('valor', ['id'=>'valor', 'class'=>'valor-mask form-control','placeholder' => 'Digite aqui o nº do documento','title'=>"Digite o número do documento escolhido",'style'=>'padding-left:10px !important;']),
        ['class'=>'col-md-8']),
    ['class'=>'form-group row','id'=>'input_valor']),

['class'=>'col-sm-4 grupo-form']);


// INPUTS PA
echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Form->label('valor', '',['class'=>"label-cdf col-md-1 col-form-label"]).
        $this->Html->tag('div',
            $this->Form->control('campo_pa', [
                'class' => 'form-control mr-1',
                'id' => 'campo_pa',
                'label' => false,
                'title' => 'Escolha tipo de Processo',
                'style' => 'padding-left:10px !important;',
                'options' => $opcoes_pa,
            ]).
            $this->Form->text('valor_pa_orgao', ['id'=>'valor_pa_orgao', 'class'=>'valor-mask form-control mr-1 valor_pa','title'=>"Digite o número do documento escolhido",'style'=>'padding-left:10px !important;width: 100px;','oninput' => 'this.value = this.value.replace(/[^0-9]/g, "");']).
            $this->Html->tag('span', '/', ['class' => 'separador', 'id' => 'sep1']).
            $this->Form->text('valor_pa_unidade_protocoladora', ['id'=>'valor_pa_unidade_protocoladora', 'class'=>'valor-mask form-control mr-1 valor_pa', 'title'=>"Digite o número do documento escolhido",'style'=>'padding-left:10px !important;width: 100px;','oninput' => 'this.value = this.value.replace(/[^0-9]/g, "");']).
            $this->Html->tag('span', '/', ['class' => 'separador', 'id' => 'sep2']).
            $this->Form->text('valor_pa_processo', ['id'=>'valor_pa_processo', 'class'=>'valor-mask form-control mr-1 valor_pa', 'title'=>"Digite o número do documento escolhido",'style'=>'padding-left:10px !important;width: 100px;','oninput' => 'this.value = this.value.replace(/[^0-9]/g, "");']).
            $this->Html->tag('span', '/', ['class' => 'separador', 'id' => 'sep3']).
            $this->Form->text('valor_pa_ano', ['id'=>'valor_pa_ano', 'class'=>'valor-mask form-control mr-1 valor_pa', 'title'=>"Digite o número do documento escolhido",'style'=>'padding-left:10px !important;width: 100px;','oninput' => 'this.value = this.value.replace(/[^0-9]/g, "");'])
        ,['class'=>'col-md-8 form-inline cxs_pa'])
    ,['class'=>'form-group row'])

,['class'=>'col-sm-10 grupo-form','id'=>'input_valor_pa','style'=>'display: none']);


echo $this->Html->tag('div',
    
    //CheckBox CNPJ RAIZ para opção CNPJ
    $this->Html->tag('div',
        $this->Html->tag('div',
            $this->Form->checkbox('raizCnpj', ['label' => false,'style'=>'margin-right: 10px;']).
            $this->Form->label('label_cnpj_raiz', 'Incluir CNPJs com a mesma raíz',['class'=>"label-raiz-cnpj col-form-label"])
        ,['class'=>'col-md-8'])
    ,['class'=>'form-group row','id'=>'cnpj-raiz','style'=>'display: none']).
    
    //DropDown Municipios para opção Taxa de Incêndio
    $this->Html->tag('div',
    $this->Form->label('label_municipios', 'Município',['class'=>"label_municipios col-md-4 col-form-label"]).
    $this->Html->tag('div',
        $this->Form->select('cidade_logradouro', ['' => 'Selecione um município'], ['class' => 'tx-municipio','id'=>'cidade_logradouro','aria-labelledby'=>'municipios'])
    ,['class'=>'col-md-8'])
,['class'=>'form-group row','id'=>'municipios','style'=>'display: none']).

    // Arcodeon de dicas de preenchimento para opção Processo Administrativo
    $this->Html->tag('div',
        $this->Html->tag('div',
            'Dicas de Preenchimento' . $this->Html->tag('i', 'expand_more', ['class' => 'material-icons icon-acordeon'])
        ,['class'=>'acordeon-titulo']).

        $this->Html->tag('div',
            $this->Html->tag('p', 'Se sua carta de notificação não informa o prefixo do processo, selecione a opção "E-".Se a numeração inicial for 66 selecione E-66, se for 77 selecione E-77, se for 88 selecione E-88, e se for 99 selecione E-99."').
            $this->Html->tag('p', 'Digite apenas números (excluindo pontos) dentro dos respectivos campos, conforme a formatação do processo enviado na carta de notificação”.')
        ,['class'=>'acordeon-conteudo'])
    ,['class'=>'acordeon','style'=>'display: none'])

,['class'=>'col-sm-4 grupo-form']);

//Plugin Captcha
echo $this->GoogleRecaptcha->display();

//Botões do formulário
echo $this->Html->tag('div',
    $this->Html->tag('span',
        $this->Form->button('Voltar', ['type' => '', 'class' => 'bt-grupo bt-voltar', 'title'=>'Voltar', 'onclick' => 'return false;'])
    ,['class' => 'bts', 'style' => 'float:left']).

    $this->Html->tag('span',
        $this->Form->submit('Avançar', ['class' => 'bt-grupo bt-buscar', 'title'=>'Avançar para resultado da consulta', 'data-callback' => 'onSubmit'])
    ,['class' => 'bts', 'style' => 'float:right']).

    $this->Html->tag('span',
        $this->Form->button('Limpar', ['type' => 'reset', 'class' => 'bt-grupo bt-limpar', 'title'=>'Limpar formulário'])
    ,['class' => 'bts', 'style' => 'float:right'])
,['class'=>'bt-group col-sm-4']);

echo $this->Form->end(); 

?>

<script>



//CARREGAR MUNICIPIOS - API
$(document).ready(function() {

    var municipiosOptions = localStorage.getItem('municipiosOptions');
    if (municipiosOptions) {
        $('#cidade_logradouro').html(municipiosOptions);
    }

    $('#campo').on('change', function() { 
        var valorCampo = $(this).val();

        if (valorCampo == 'Taxa de Incêndio') {
           
            $.ajax({
                url: 'http://desenvda.in.pge.rj.gov.br/fsw_da/da_portal_contribuinte_desenvolvimento/servicos_portal_contribuinte/api/listarMunicipios',
                dataType: 'json',
                success: function(data) {
                    var options = '<option value="">Selecione um município</option>';
                    $.each(data.municipios, function(index, value) {
                        options += '<option value="' + value.municipio + '">' + value.municipio + '</option>';
                    });
                    $('#cidade_logradouro').html(options);
                    $('#municipios').show();

                    // Salvar opções no armazenamento local do navegador
                    localStorage.setItem('municipiosOptions', options);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Erro na requisição AJAX: ' + textStatus);
                }
            });

            $('#municipios').show();
        } else {
            $('#cidade_logradouro').html('');
            $('#municipios').hide();

            // Remover opções do armazenamento local do navegador
            localStorage.removeItem('municipiosOptions');
        }
    });
});

</script>







