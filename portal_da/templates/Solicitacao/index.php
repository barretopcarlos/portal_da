<?php 
echo $this->Html->script('jquery-3.6.4.min');
echo $this->Html->script('jquery.inputmask.min');
echo $this->Html->script('bootstrap.min');
echo $this->Html->script('index_solicitacao');

echo $this->Html->css(['solicitacao/index','solicitacao/modal']);
echo $this->Html->script('https://www.google.com/recaptcha/api.js?hl=pt');

$conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#campo', ['class' => 'skip-main']);
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


//MENSAGENS D ERROS 
echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
        $this->Html->tag('p', 'Verifique o formulário e preencha corretamente os campos', ['class' => 'texto-info-erro'])
    , ['class' => 'info-erro'])

, ['id'=>'erro-cpf','class' => 'col-sm-8 box-info-erro erro-cpf', 'style'=>'display:none']);

echo $this->Html->tag('div',

    $this->Html->tag('div',
        $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
        $this->Html->tag('p', 'Por favor, marque o captcha para prosseguir.', ['class' => 'texto-info-erro'])
    , ['class' => 'info-erro'])

, ['id'=>'erro-captcha','class' => 'col-sm-8 box-info-erro erro-cpf', 'style'=>'display:none']);



echo $this->Form->create(null, [
    'url' => ['action' => 'verificaContribuinte'], 
    'type' => 'get', 
    'id' => 'form_solicitacao', 
    'onsubmit' => 'event.preventDefault(); onSubmit();'
]) ;

echo $this->Html->div('',

    // CAMPO
    $this->Html->div('',
        $this->Form->label('label_campo', 'Selecione o tipo de contribuinte', ['class' => "label-cdf col-md-4 col-form-label", 'title' => 'Consulta Por']) .
        $this->Html->div('',
            $this->Form->control('campo', ['class' => "form-control campo-em-foco", 'id' => 'campo', 'label' => false, 'options' => ['Selecione uma opção','Pessoa Física', 'Pessoa Jurídica'], "title" => "Escolha uma opção de documento", 'style' => 'padding-left:10px !important;']),
        ['class' => 'col-md-8']),
    ['class' => 'form-group row group-campo']) .

    // VALOR´
    $this->Html->div('',
        $this->Form->label('valor', 'Documento do contribuinte', ['class' => "label-cdf col-md-4 col-form-label", 'id' => 'label_valor']) .
        $this->Html->div('',
            $this->Form->text('valor', ['id' => 'valor', 'class' => 'valor-mask form-control', 'placeholder' => 'Digite aqui o nº do documento', 'title' => "Digite o número do documento", 'style' => 'padding-left:10px !important;']),
        ['class' => 'col-md-8']),
    ['class' => 'form-group row', 'id' => 'input_valor']) .

    //Mensagem validação CPF
    $this->Html->div('',
    '',
    ['class' => 'mensagem-cpf', 'id' => 'mensagem-cpf']).

    // EMAIL
    $this->Html->div('',
        $this->Form->label('email_label', 'E-mail', ['class' => "label-cdf col-md-4 col-form-label", 'id' => 'label_email']) .
        $this->Html->div('',
            $this->Form->text('email', ['id' => 'email', 'class' => 'valor-mask form-control validate-email', 'placeholder' => 'Digite aqui um e-mail válido', 'title' => "Digite aqui um e-mail válido", 'style' => 'padding-left:10px !important;']),
        ['class' => 'col-md-8']),
    ['class' => 'form-group row', 'id' => 'input_email']).

    //Mensagem validação EMAIL
    $this->Html->div('',
    '',
    ['class' => 'mensagem-email', 'id' => 'mensagem-email']),

['class' => 'col-sm-4 grupo-form']);

//Loading
echo $this->Html->tag('div', '<div class="loading-content"><img src="' . $this->Url->webroot('img/carregar.gif') . '" class="loading-spinner" alt="Carregando..."><div class="loading-text">Carregando, por favor aguarde.</div></div>', ['id' => 'loading', 'class' => 'loading-overlay', 'style' => 'display:none;']);


// Plugin Captcha
echo $this->GoogleRecaptcha->display();

// Botões do formulário
echo $this->Html->tag('div',
    $this->Html->tag('span',
        $this->Form->button('Voltar', ['type' => '', 'class' => 'bt-grupo bt-voltar', 'title'=>'Voltar', 'onclick' => 'return false;'])
    ,['class' => 'bts', 'style' => 'float:left']).

    $this->Html->tag('span',
        $this->Form->submit('Avançar', ['class' => 'bt-grupo bt-buscar', 'title'=>'Avançar para resultado da consulta', 'data-callback' => 'onSubmit'])
    ,['class' => 'bts', 'style' => 'float:right']).

    $this->Html->tag('span',
        $this->Form->button('Limpar', ['type' => 'reset', 'class' => 'bt-grupo bt-limpar', 'title' => 'Limpar formulário', 'id' => 'btn-limpar'])
    , ['class' => 'bts', 'style' => 'float:right'])
, ['class' => 'bt-group col-sm-4']);

echo $this->Form->end();

?>


<script>

    document.getElementById('form_solicitacao').addEventListener('submit', function(event) {

        var valor = document.getElementById("valor").value;
        var email = document.getElementById("email").value;
        var mensagemCpf = document.getElementById("mensagem-cpf").textContent;
        var mensagemEmail = document.getElementById("mensagem-email").textContent;
        var recaptchaResponse = document.getElementById('g-recaptcha-response').value;

        // Verifica as condições de validação
        if (valor === "" || mensagemCpf === "CPF inválido." || mensagemEmail === "E-mail inválido." || email === "" ) {
            // Exibe a div de erro
            var divErroCpf = document.getElementById("erro-cpf");
            divErroCpf.style.display = "block";

            // Impede o envio do formulário
            event.preventDefault();

        }else if(recaptchaResponse === null || recaptchaResponse === ''){

            // Exibe a div de erro
            var divErroCpf = document.getElementById("erro-captcha");
            divErroCpf.style.display = "block";

            // Impede o envio do formulário
            event.preventDefault();

        }else{
            document.getElementById("loading").style.display = "flex";

            event.preventDefault(); // Impede o envio automático do formulário

            var recaptchaResponse = document.getElementById('g-recaptcha-response').value;

            // Adiciona o valor do token como um campo oculto no formulário
            var form = document.getElementById('form_solicitacao');
            var input_cap = document.createElement('input');
            input_cap.type = 'hidden';
            input_cap.name = 'g-recaptcha-response';
            input_cap.value = recaptchaResponse;
            form.appendChild(input_cap);

            // Envia o formulário
            form.submit();

        }

    });


    function validarCPF(cpf) {

        cpf = cpf.replace(/[^\d]+/g, ''); // Remove caracteres especiais do CPF

        if (cpf.length !== 11) {
            return false;
        }
        var sum = 0;
        var remainder;
        for (i = 1; i <= 9; i++) {
            sum = sum + parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }
        remainder = (sum * 10) % 11;
        if ((remainder === 10) || (remainder === 11)) {
            remainder = 0;
        }
        if (remainder !== parseInt(cpf.substring(9, 10))) {
            return false;
        }
        sum = 0;
        for (i = 1; i <= 10; i++) {
            sum = sum + parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }
        remainder = (sum * 10) % 11;
        if ((remainder === 10) || (remainder === 11)) {
            remainder = 0;
        }
        if (remainder !== parseInt(cpf.substring(10, 11))) {
            return false;
        }
        return true;
    }

 // Função para validar CNPJ
function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g,'');
    
    if (cnpj.length != 14)
        return false;
    
    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0, tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    
    if (resultado != digitos.charAt(0))
        return false;
    
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;
    
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    
    if (resultado != digitos.charAt(1))
        return false;
    
    return true;
}

// Função para formatar o CNPJ (remover caracteres não numéricos e adicionar zeros à esquerda)
function formatarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g,'');
    while (cnpj.length < 14) {
        cnpj = '0' + cnpj;
    }
    return cnpj;
}

// Exibe a mensagem de validação do CPF/CNPJ
function exibirMensagemValidacao(cpfCnpj, tipo) {
    var mensagemElement = document.getElementById('mensagem-cpf');

    if (cpfCnpj === '') {
        mensagemElement.innerHTML = ''; // Limpa a mensagem quando o campo está vazio
        return;
    }

    var valido;
    if (tipo === '1') {
        // Se for CPF
        valido = validarCPF(cpfCnpj);
    } else if (tipo === '2') {
        // Se for CNPJ
        cpfCnpj = formatarCNPJ(cpfCnpj); // Formata o CNPJ antes da validação
        valido = validarCNPJ(cpfCnpj);
    } else {
        mensagemElement.innerHTML = 'Tipo inválido.';
        mensagemElement.style.color = 'red';
        return;
    }

    if (valido) {
        mensagemElement.innerHTML = tipo === '1' ? 'CPF válido.' : 'CNPJ válido.';
        mensagemElement.style.color = 'green'; // Define a cor como verde
    } else {
        mensagemElement.innerHTML = tipo === '1' ? 'CPF inválido.' : 'CNPJ inválido.';
        mensagemElement.style.color = 'red'; // Define a cor como vermelho
    }
}

// Chama a função de validação sempre que o valor do campo CPF/CNPJ for alterado
var inputField = document.getElementById('valor');
var selectField = document.getElementById('campo');

inputField.addEventListener('input', function () {
    exibirMensagemValidacao(this.value, selectField.value);
});

inputField.addEventListener('paste', function (event) {
    var pastedValue = event.clipboardData.getData('text');
    setTimeout(function () {
        inputField.value = pastedValue; // Define o valor colado diretamente
        exibirMensagemValidacao(inputField.value, selectField.value);
    }, 0);
});

selectField.addEventListener('change', function () {
    exibirMensagemValidacao(inputField.value, this.value);
});



    //Botão limpar
    var btnLimpar = document.getElementById("btn-limpar");

    // Função para desmarcar o reCAPTCHA
    function resetRecaptcha() {
        grecaptcha.reset();
    }

    btnLimpar.addEventListener("click", function() {

        resetRecaptcha();

        var mensagemCpf = document.getElementById("mensagem-cpf");
        var mensagemEmail = document.getElementById("mensagem-email");
        var divErroCpf = document.getElementById("erro-cpf");
        var divErroCaptcha = document.getElementById("erro-captcha");

        var valorInput = $('#valor');
        var valorEmail = $('#email');
            
        valorInput.prop('disabled', true); 
        valorEmail.prop('disabled', true); 
        mensagemCpf.innerText = ""; // Limpa o texto da mensagem-cpf
        mensagemEmail.innerText = ""; // Limpa o texto da mensagem-email
        divErroCpf.style.display = "none"; //Limpa mensagens de erro
        divErroCaptcha.style.display = "none"; //Limpa mensagens de erro

    });

    $(document).ready(function() {

        //Verifica e habilita/desabilita o campo "valor"
        function verificarTipoContribuinte() {
            var campo = $('#campo').val();
            var valorInput = $('#valor');
            var valorEmail = $('#email');
            
            if (campo === '1' || campo === '2') {
                valorInput.prop('disabled', false); // Habilita o campo "valor"
                valorEmail.prop('disabled', false); // Habilita o campo "email"
            } else {
                valorInput.prop('disabled', true); // Desabilita o campo "valor"
                valorEmail.prop('disabled', true); // Desabilita o campo "email"
            }
        }

        // Verifica o tipo de contribuinte quando a página é carregada
        verificarTipoContribuinte();

        // Verifica o tipo de contribuinte quando o valor do campo "campo" muda
        $('#campo').change(function() {
            verificarTipoContribuinte();
        });

        
        var valor = $('#valor'); // Captura o input de id = valor
        
        // Adiciona o evento change no input de id = campo
        $('#campo').on('change', function() {

            valor.val(''); // Limpa o valor atual do input
        
            // Verifica se há um estado salvo da máscara 
            var savedMask = localStorage.getItem('mask');
            if (savedMask) {
                valor.mask(savedMask);
            }
        
            // Aplica a máscara correspondente à opção selecionada
            switch ($(this).val()) {
            case '1':
                valor.mask('000.000.000-00');
                break;
            case '2':
                valor.mask('00.000.000/0000-00');
                break;
            default:
                valor.unmask(); // Remove a máscara apenas para outras opções
            }
        
            if (!valor.valid()) {
            alert('O valor digitado não está de acordo com a máscara correspondente.');
            return false; // Impede que o formulário seja enviado
            }
        });
        
        // Verifica o campo atual e completa com zeros à esquerda aplicando máscara
        valor.on('blur', function() {

            var cpfInput = document.getElementById('valor');
            var mensagemElement = document.getElementById('mensagem-cpf');

            if($('#valor').val() === ''){
                mensagemElement.innerHTML = '';
                mensagemElement.innerText = "";
            }

            if ($('#campo').val() === '1' && valor.val().length < 14 && valor.val().length > 0) {
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(11, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); // Aplica a máscara de CPF
                valor.val(maskedValue);

            }else if($('#campo').val() === '2' && valor.val().length < 18 && valor.val().length > 0){
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(14, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'); // Aplica a máscara de CNPJ
                valor.val(maskedValue);

            }
        });
        
        // Verifica se há um estado salvo da máscara
        var savedMask = localStorage.getItem('mask');
        if (savedMask) {
            valor.mask(savedMask);
        }
        
        // Salva o estado da máscara antes de enviar o formulário
        $(window).on('beforeunload', function() {
            var currentMask = valor.data('mask');
            localStorage.setItem('mask', currentMask);
        });
        
        // Restaura o estado da máscara ao carregar a página novamente
        $(window).on('load', function() {
            var savedMask = localStorage.getItem('mask');
            if (savedMask) {
            valor.mask(savedMask);
            }
        });

    
        //Valida o e-mail
        function validarEmail(email) {
            // Expressão regular para validar o formato do e-mail
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        //Exibe a mensagem de validação do e-mail
        function exibirMensagemValidacaoEmail(email) {
            var emailInput = document.getElementById('email');
            var mensagemElement = document.getElementById('mensagem-email');

            if (email.length === 0) {
                mensagemElement.innerHTML = ''; // Limpa a mensagem quando o campo está vazio
            } else if (validarEmail(email)) {
                mensagemElement.innerHTML = 'E-mail válido.';
                mensagemElement.style.color = "green"; // Define a cor como verde
            } else {
                mensagemElement.innerHTML = 'E-mail inválido.';
                mensagemElement.style.color = "red"; // Define a cor como vermelho
            }
        }

        // Chama a função de validação sempre que o valor do campo de e-mail for alterado
        var emailInput = document.getElementById("email");
            emailInput.addEventListener("input", function() {
            exibirMensagemValidacaoEmail(this.value);
        });

            // Adicione um novo event listener para o evento paste no campo #valor
            $('#valor').on('paste', function (e) {
                var campo = $('#campo').val();
                var pasteData = e.originalEvent.clipboardData.getData('text/plain');
                var cleanValue = pasteData.replace(/[^\d]+/g, ''); // Remove caracteres não numéricos
                var valorInput = $(this);

                // Aplica a máscara correspondente à opção selecionada
                if (campo === '1') {
                    var maskedValue = cleanValue.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); // Aplica a máscara de CPF
                    valorInput.val(maskedValue);
                } else if (campo === '2') {
                    var maskedValue = cleanValue.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'); // Aplica a máscara de CNPJ
                    valorInput.val(maskedValue);
                }

                // Chama a função de validação
                exibirMensagemValidacao(valorInput.val(), campo);
            });



    });




    $(document).ready(function() {
        // Quando o botão for clicado, vamos abrir o dropbox
        $('.skip-main').click(function() {
            $('#conteudo-principal').show(); // Mostrar o dropbox
        });

        // Quando o botão receber foco (por exemplo, quando for acessado por teclado),
        // também vamos abrir o dropbox
        $('.skip-main').focus(function() {
            $('#conteudo-principal').show(); // Mostrar o dropbox
        });

        // Quando o dropbox perder o foco (ou seja, o usuário clicar em outro lugar da página),
        // vamos escondê-lo novamente
        $('#conteudo-principal').blur(function() {
            $(this).hide(); // Esconder o dropbox
        });
    });
                    


</script>








