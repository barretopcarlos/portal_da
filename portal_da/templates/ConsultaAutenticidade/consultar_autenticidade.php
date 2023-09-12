<?php 
echo $this->Html->script('jquery-3.6.4.min');
echo $this->Html->script('jquery.inputmask.min');
echo $this->Html->script('bootstrap.min');

echo $this->Html->css(['consultaAutenticidade/consultaAutenticidade']);
echo $this->Html->script('https://www.google.com/recaptcha/api.js?hl=pt');

$conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#principal', ['class' => 'skip-main']);
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

$data_consulta_autenticidade = date('d/m/Y \à\s H\hi\m\i\n');

if(isset($resultado) && $resultado != null ){   
    
    $codigo_certidao = !empty($resultado['certidao']['cod_autenticidade_certidao']) ? $resultado['certidao']['cod_autenticidade_certidao'] : '-';
    $tipo_certidao = !empty($resultado['certidao']['nome_certidao_tipo']) ? $resultado['certidao']['nome_certidao_tipo'] : '-';
    
    if($tipo_certidao == 'CPPF' || $tipo_certidao == 'CPPJ'){
        $tipo_certidao = 'Certidão Positiva';
    }else if($tipo_certidao == 'CNPF' || $tipo_certidao == 'CNPJ'){
        $tipo_certidao = 'Certidão Negativa';
    }

    $documento_contribuinte = !empty($resultado['certidao']['documento_contribuinte']) ? $resultado['certidao']['documento_contribuinte'] : '-';
   
    $tipo_contribuinte = !empty($resultado['certidao']['nome_contribuinte_tipo']) ? $resultado['certidao']['nome_contribuinte_tipo'] : '-';

    if($tipo_contribuinte == 'PF'){
        $tipo_contribuinte = 'Pessoa Física';
    }else if($tipo_contribuinte == 'PJ'){
        $tipo_contribuinte = 'Pessoa Jurídica';
    }

    $nome_contribuinte = !empty($resultado['certidao']['nome_contribuinte']) ? $resultado['certidao']['nome_contribuinte'] : '-';
    $data_consulta_certidao = !empty($resultado['certidao']['data_hora_consulta']) ? $resultado['certidao']['data_hora_consulta'] : '-';
    $data_validade = !empty($resultado['certidao']['certidao_validade']) ? $resultado['certidao']['certidao_validade'] : '-';

    //formatando data certidao
    $data_hora_certidao_formatada = DateTime::createFromFormat('Y-m-d H:i:s.u', $data_consulta_certidao);
    $data_consulta_certidao = $data_hora_certidao_formatada->format('d/m/Y \à\s H\hi\m\i\n');

    //formatando data validade da certidao
    $data_hora_validade_formatada = new DateTime($data_validade);
    $data_hora_validade_formatada = $data_hora_validade_formatada->format('d/m/Y');

    //formatando documento
    if(isset($resultado['certidao']['documento_contribuinte']) && $resultado['certidao']['documento_contribuinte'] != null){
        if (strlen($documento_contribuinte) > 11) {
            $cnpj = str_pad($documento_contribuinte, 14, '0', STR_PAD_LEFT);
            $documento_contribuinte = substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
            $tipo_documento = 'CNPJ';
        } else if (strlen($documento_contribuinte) <= 11) {
            $cpf = str_pad($documento_contribuinte, 11, '0', STR_PAD_LEFT);
            $documento_contribuinte = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
            $tipo_documento = 'CPF';
        }
    }

}

$valida = isset($resultado['validade']['valida']) ? $resultado['validade']['valida'] : null;

if(isset($resultado) && $resultado !== null){

    if($resultado['validade']['valida'] === true){

        echo $this->Html->tag('div',

            $this->Html->tag('div',
                $this->Html->tag('i', 'check_circle', ['class' => 'material-icons icon-check_circle']).
                $this->Html->tag('p', 'Confira os dados abaixo para verificar a autenticidade da certidão consultada.', ['class' => 'texto-solicitacao-aprovada'])
            , ['class' => 'solicitacao-aprovada'])

        , ['class' => 'col-sm-6']);

    }else {

        echo $this->Html->tag('div',

        $this->Html->tag('div',
            $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
            $this->Html->tag('p', 'Certidão consultada está fora da validade, por favor, solicite uma nova certidão.', ['class' => 'texto-info-erro'])
        , ['class' => 'info-erro'])

    , ['id'=>'erro-codigo','class' => 'col-sm-8 box-info-erro erro-codigo']);

    }

    
    echo $this->Html->tag('div',

    $this->Html->tag('p', '<b>Data e hora da consulta: </b>'. $data_consulta_autenticidade, ['class' => 'label-pedido', 'id'=>'principal']).  
    $this->Html->tag('p', '<b>Código da Certidão: </b>'. $codigo_certidao, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Tipo de Certidão: </b>'. $tipo_certidao, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Tipo de Contribuinte: </b>'. $tipo_contribuinte, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Nome do Contribuinte: </b>'. $nome_contribuinte, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Nº do ' . $tipo_documento .': </b>'. $documento_contribuinte, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Data e hora da emissão da certidão: </b>'. $data_consulta_certidao, ['class' => 'label-pedido']).
    $this->Html->tag('p', '<b>Data da validade da certidão: </b>'. $data_hora_validade_formatada, ['class' => 'label-pedido'])

, ['class' => 'dados-pedido']);


echo $this->Html->tag('div',

    $this->Html->tag('span',
    $this->Html->link('Voltar', ['controller' => 'ConsultaAutenticidade', 'action' => 'index'], [
        'class' => 'bt-grupo bt-voltar',
        'title' => 'Voltar'
    ]),    ['class' => 'bts']).

    $this->Html->tag('button',
    '<i class="material-icons icon-print">local_printshop</i>'. 'Imprimir',
    [
        'type' => 'button',
        'class' => 'bt-solicitacao-grupo bt-imprimir-certidao',
        'title' => 'Imprimir Certidão',
        'id' => 'btn-imprimir-certidao',
        'onclick' => 'imprimirCertidao();'
    ]
)


    
, ['class' => 'bt-group col-sm-4', 'id'=>'conteudo-principal']);


}

?>


<script>

//REFATORAR
function imprimirCertidao() {

    var params = 'data_consulta_autenticidade=<?php echo urlencode($data_consulta_autenticidade); ?>' +
                 '&codigo_certidao=<?php echo urlencode($codigo_certidao); ?>' +
                 '&tipo_certidao=<?php echo urlencode($tipo_certidao); ?>' +
                 '&tipo_contribuinte=<?php echo urlencode($tipo_contribuinte); ?>' +
                 '&nome_contribuinte=<?php echo urlencode($nome_contribuinte); ?>' +
                 '&documento_contribuinte=<?php echo urlencode($documento_contribuinte); ?>' +
                 '&tipo_documento=<?php echo urlencode($tipo_documento); ?>' +
                 '&data_consulta_certidao=<?php echo urlencode($data_consulta_certidao); ?>' +
                 '&data_hora_validade_formatada=<?php echo urlencode($data_hora_validade_formatada); ?>' +
                 '&valida=<?php echo urlencode($valida); ?>';

    var url = "<?php echo $this->Url->build(['controller' => 'ConsultaAutenticidade', 'action' => 'imprimirConsultaAutenticidade']); ?>?" + params;
    
    window.open(url, "_blank");
}


document.addEventListener('DOMContentLoaded', function () {
    const inputValor = document.getElementById('valor');
    const mensagemCodigo = document.getElementById('mensagem-codigo');

    inputValor.addEventListener('input', function () {
        const codigo = inputValor.value.trim();

        // Expressão regular para verificar o formato do código
        const regex = /^[0-9]{4}-[0-9]{4}-[A-Za-z]{4}-[0-9]{3,}$/;

        if (codigo === "") { // Verifica se o campo está vazio
            mensagemCodigo.textContent = ''; // Define o valor da mensagem como vazio
            mensagemCodigo.classList.remove('codigo-invalido');
            mensagemCodigo.classList.remove('codigo-valido');
        } else if (regex.test(codigo)) {
            mensagemCodigo.textContent = 'Código válido';
            mensagemCodigo.classList.remove('codigo-invalido');
            mensagemCodigo.classList.add('codigo-valido');
        } else {
            mensagemCodigo.textContent = 'Código inválido';
            mensagemCodigo.classList.remove('codigo-valido');
            mensagemCodigo.classList.add('codigo-invalido');
        }
    });
});



    document.getElementById('form_autenticidade').addEventListener('submit', function(event) {

        var valor = document.getElementById("valor").value;
        var mensagemCodigo = document.getElementById("mensagem-codigo").textContent;
        var recaptchaResponse = document.getElementById('g-recaptcha-response').value;

        // Verifica as condições de validação
        if (valor === "" || mensagemCodigo === "Código inválido.") {
            // Exibe a div de erro
            var divErroCpf = document.getElementById("erro-codigo");
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
        const mensagemCodigo = document.getElementById('mensagem-codigo');

        var valorInput = $('#valor');
        mensagemCodigo.textContent = ""; // Limpa o texto da mensagem-codigo
        divErroCodigo.style.display = "none"; //Limpa mensagens de erro
        divErroCaptcha.style.display = "none"; //Limpa mensagens de erro

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








