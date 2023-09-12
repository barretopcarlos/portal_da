<?php 
echo $this->Html->script('jquery-3.6.4.min');
echo $this->Html->script('jquery.inputmask.min');
echo $this->Html->script('bootstrap.min');

echo $this->Html->css(['consultaAutenticidade/index']);
echo $this->Html->script('https://www.google.com/recaptcha/api.js?hl=pt');

$conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#valor', ['class' => 'skip-main']);
echo $conteudo_principal;

// Exemplo BreadCrumb - Alterar quando tiver home do site
echo $this->Html->tag('div',
    $this->Html->tag('span', 'Home', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']).
    $this->Html->tag('span', 'Portal do contribuinte', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']) .
    $this->Html->tag('span', 'Consulta de Autenticidade de Certidão', ['class' => 'span-bb']), ['class' => 'exemplo_breadcrumb no-print']);

//Titulo
echo $this->Html->tag('h1', 'Consulta de Autenticidade de Certidão', ['title' => 'Consulta de Autenticidade de Certidões de Regularidade Fiscal','aria-label'=>'Consulta de Autenticidade de Certidões de Regularidade Fiscal']).
$this->Html->tag('br'),
$this->Html->tag('h2', 'Permite confirmar a autenticidade de uma certidão de regularidade fiscal.', ['title' => 'Utilize esta funcionalidade para consultar a atutenticidade de certidões de regularidade fiscal.']);


//MENSAGENS D ERROS 

if(isset($sucesso) && $sucesso == false){

    echo $this->Html->tag('div',

        $this->Html->tag('div',
            $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
            $this->Html->tag('p', 'Verifique o código digitado, sua certidão não foi localizada ou não é autêntica.', ['class' => 'texto-info-erro'])
        , ['class' => 'info-erro'])

    , ['id'=>'erro-codigo','class' => 'col-sm-8 box-info-erro erro-codigo']);

    $this->getRequest()->getSession()->write('sucesso_certidao', true);

}


echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
        $this->Html->tag('p', 'O preenchimento do código da certidão é obrigatório', ['class' => 'texto-info-erro'])
    , ['class' => 'info-erro'])

, ['id'=>'erro-codigo-vazio','class' => 'col-sm-8 box-info-erro erro-codigo-vazio', 'style'=>'display:none']);

echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
        $this->Html->tag('p', 'Código da certidão incorreto, verifique o valor digitado.', ['class' => 'texto-info-erro'])
    , ['class' => 'info-erro'])

, ['id'=>'erro-codigo','class' => 'col-sm-8 box-info-erro erro-codigo', 'style'=>'display:none']);

echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
        $this->Html->tag('p', 'Por favor, marque o captcha para prosseguir.', ['class' => 'texto-info-erro'])
    , ['class' => 'info-erro'])

, ['id'=>'erro-captcha','class' => 'col-sm-8 box-info-erro erro-captcha', 'style'=>'display:none']);


// INICIO DO FORMULARIO

echo $this->Form->create(null, [
    'url' => ['action' => 'consultarAutenticidade'], 
    'type' => 'get', 
    'id' => 'form_autenticidade', 
    'onsubmit' => 'event.preventDefault(); onSubmit();'
]) ;

echo $this->Html->div('',

    // VALOR
    $this->Html->div('',
        $this->Form->label('valor', 'Código da Certidão', ['class' => "label-cdf col-md-2 col-form-label", 'id' => 'label_valor']) .
        $this->Html->div('',
            $this->Form->text('valor', ['id' => 'valor', 'class' => 'valor-mask form-control', 'placeholder' => 'Digite o código da Certidão. Ex: 0000-2023-AAAA-0000', 'title' => "Digite o número do código da Certidão", 'style' => 'padding-left:10px !important;']),
        ['class' => 'col-md-10']),
    ['class' => 'form-group row', 'id' => 'input_valor']) .

    //Mensagem validação código
    $this->Html->div('',
    '',
    ['class' => 'mensagem-codigo', 'id' => 'mensagem-codigo']),

['class' => 'col-sm-6 grupo-form']);


// Plugin Captcha
echo $this->GoogleRecaptcha->display();

// Botões do formulário
echo $this->Html->tag('div',
    $this->Html->tag('span',
        $this->Form->button('Voltar', ['type' => '', 'class' => 'bt-grupo bt-voltar', 'title'=>'Voltar', 'onclick' => 'return false;'])
    ,['class' => 'bts', 'style' => 'float:left']).

    $this->Html->tag('span',
        $this->Form->submit('Pesquisar', ['class' => 'bt-grupo bt-buscar', 'title'=>'Avançar para resultado da consulta', 'data-callback' => 'onSubmit'])
    ,['class' => 'bts', 'style' => 'float:right']).

    $this->Html->tag('span',
        $this->Form->button('Limpar', ['type' => 'reset', 'class' => 'bt-grupo bt-limpar', 'title' => 'Limpar formulário', 'id' => 'btn-limpar'])
    , ['class' => 'bts', 'style' => 'float:right'])
, ['class' => 'bt-group col-sm-4']);

echo $this->Form->end();

?>


<script>

$(document).ready(function() {

    //Pegar parametro na URL e preencher o input
    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

    // pegaro valor na URL
    var valorFromURL = getParameterByName('valor');

    if (valorFromURL) {
        $('#valor').val(valorFromURL); // Preenche o campo de input
    }


    //MASCARA PARA COD DE CERTIDAO

    $('#valor').on('input', function(e) {
        var inputValue = $(this).val();
        
        // Remove caracteres especiais, exceto letras e números
        var cleanedValue = inputValue.replace(/[^a-zA-Z0-9]/g, '');
        
        // Divide o valor em grupos
        var groups = [];
        var index = 0;
        var groupLengths = [4, 4, 4, cleanedValue.length - 12];
        
        for (var i = 0; i < groupLengths.length; i++) {
            var groupLength = groupLengths[i];
            var group = cleanedValue.substr(index, groupLength);
            if (group) {
                groups.push(group);
                index += groupLength;
            }
        }
        
        // Constrói a máscara formatada
        var formattedValue = groups.join('-');
        
        // Insere o valor formatado no campo
        $(this).val(formattedValue);
    });


});

// VERIFICAÇÃO DO QUE FOI DIGITADO OU COLADO NO CAMPO

document.addEventListener('DOMContentLoaded', function () {
    const inputValor = document.getElementById('valor');
    const mensagemCodigo = document.getElementById('mensagem-codigo');

    function validateCodigo(codigo) {
        // verificar o formato do código
        const regex = /^[0-9]{4}-[0-9]{4}-[A-Za-z]{4}-[0-9]{1,}$/;

        if (codigo === "") { // Verifica se o campo está vazio
            mensagemCodigo.textContent = ''; 
            mensagemCodigo.classList.remove('codigo-invalido');
            mensagemCodigo.classList.remove('codigo-valido');
        } else if (codigo.match(regex)) {
            mensagemCodigo.textContent = 'Código válido';
            mensagemCodigo.classList.remove('codigo-invalido');
            mensagemCodigo.classList.add('codigo-valido');
        } else {
            mensagemCodigo.textContent = 'Código inválido';
            mensagemCodigo.classList.remove('codigo-valido');
            mensagemCodigo.classList.add('codigo-invalido');
        }
    }

    inputValor.addEventListener('input', function () {
        inputValor.value = inputValor.value.trim();
        const codigo = inputValor.value.trim();
        var maxLength = 100; // LIMITE DE CARACTERES PARA O COD CERTIDAO
        
        if (inputValor.value.length > maxLength) {
            inputValor.value = inputValor.value.substring(0, maxLength);
        }

        validateCodigo(codigo);
    });

    inputValor.addEventListener('paste', function (e) {
        // Aguarda um momento para que o valor seja colado antes de validar
        setTimeout(function () {
            inputValor.value = inputValor.value.trim();
            const codigo = inputValor.value.trim();
            var maxLength = 100; // 
            
            if (inputValor.value.length > maxLength) {
                inputValor.value = inputValor.value.substring(0, maxLength);
            }

            validateCodigo(codigo);
        }, 0);
    });
});





    document.getElementById('form_autenticidade').addEventListener('submit', function(event) {

        var valor = document.getElementById("valor").value;
        var mensagemCodigo = document.getElementById("mensagem-codigo").textContent;
        var recaptchaResponse = document.getElementById('g-recaptcha-response').value;

        // Verifica as condições de validação
        if (valor === ""){

            // Exibe a div de erro
            var divErroCodigoVazio = document.getElementById("erro-codigo-vazio");
            divErroCodigoVazio.style.display = "block";

            // Impede o envio do formulário
            event.preventDefault();
        
        
        }else if(mensagemCodigo === "Código inválido") {
            
            //Limpa erro de codigo vazio
            var divErroCodigoVazio = document.getElementById("erro-codigo-vazio");
            divErroCodigoVazio.style.display = "none"; //Limpa mensagens de erro

            // Exibe a div de erro
            var divErroCodigoInvalido = document.getElementById("erro-codigo");
            divErroCodigoInvalido.style.display = "block";

            // Impede o envio do formulário
            event.preventDefault();

        }else if(recaptchaResponse === null || recaptchaResponse === ''){

            // Exibe a div de erro
            var divErroCaptcha = document.getElementById("erro-captcha");
            divErroCaptcha.style.display = "block";

            // Impede o envio do formulário
            event.preventDefault();

        }else{
            
            event.preventDefault(); // Impede o envio automático do formulário

            var recaptchaResponse = document.getElementById('g-recaptcha-response').value;

            // Adiciona o valor do token como um campo oculto no formulário
            var form = document.getElementById('form_autenticidade');
            var input_cap = document.createElement('input');
            input_cap.type = 'hidden';
            input_cap.name = 'g-recaptcha-response';
            input_cap.value = recaptchaResponse;
            form.appendChild(input_cap);

            // Envia o formulário
            form.submit();

        }

    });



    //Botão limpar
    var btnLimpar = document.getElementById("btn-limpar");

    // Função para desmarcar o reCAPTCHA
    function resetRecaptcha() {
        grecaptcha.reset();
    }

    btnLimpar.addEventListener("click", function() {

        resetRecaptcha();

        var divErroCodigo = document.getElementById("erro-codigo");
        var divErroCaptcha = document.getElementById("erro-captcha");
        var divErroCodigoVazio = document.getElementById("erro-codigo-vazio");
        const mensagemCodigo = document.getElementById('mensagem-codigo');

        var valorInput = $('#valor');
        mensagemCodigo.textContent = ""; 
        divErroCodigo.style.display = "none"; //Limpa mensagens de erro
        divErroCaptcha.style.display = "none"; //Limpa mensagens de erro
        divErroCodigoVazio.style.display = "none"; //Limpa mensagens de erro

    });

    $(document).ready(function() {
        // Quando o botão for clicado, abre o dropbox
        $('.skip-main').click(function() {
            $('#conteudo-principal').show(); // Mostra o dropbox
        });

        // Quando o botão receber foco
        $('.skip-main').focus(function() {
            $('#conteudo-principal').show(); // Mostra o dropbox
        });

        // Quando o dropbox perder o foco, esconde
        $('#conteudo-principal').blur(function() {
            $(this).hide(); // Esconder o dropbox
        });
    });
                    


</script>








