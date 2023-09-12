<?php

  echo $this->Html->script('jquery-3.6.4.min');
  echo $this->Html->script('jquery.inputmask.min');
  echo $this->Html->script('bootstrap.min');
  echo $this->Html->script('index_solicitacao');
  echo $this->Html->css(['solicitacao/verifica_contribuinte', 'solicitacao/modal']);

  //variável para mandar como oculto no formulário.
  $tipo_contribuinte = null;

  if (isset($parametros['email']) && !empty($parametros['email'])) {
    $email = $parametros['email'];
  } else {
    $email = null;
  }

  if($parametros['campo'] === '1'){ //PESSOA FISICA

    $tipo_contribuinte = 'PESSOA FISICA';

    if (isset($resultado['data'][0]['nome']) && !empty($resultado['data'][0]['nome'])) {
      $nome = $resultado['data'][0]['nome'];
    } else {
      $nome = null;
    }
    
      
    if (isset($resultado['data'][0]['cpf']) && !empty($resultado['data'][0]['cpf'])) {
      $documento = $resultado['data'][0]['cpf'];
    } else {
      $documento = null;
    }

    if($nome == false && $documento == false){
      $nome = null;
      $documento = $parametros['valor'];
    }

    $documento = preg_replace('/[^0-9]/', '', $documento);
    $documento_formatado = substr_replace($documento, '.', 3, 0);
    $documento_formatado = substr_replace($documento_formatado, '.', 7, 0);
    $documento_formatado = substr_replace($documento_formatado, '-', 11, 0);

  }else if($parametros['campo'] === '2'){ //PESSOA JURIDICA

    $tipo_contribuinte = 'PESSOA JURIDICA';

    if (isset($resultado['data']['nome_fantasia']) && !empty($resultado['data']['nome_fantasia'])) {
      $nome = $resultado['data']['nome_fantasia'];
    } else {
      $nome = null;
    }
    
      
    if (isset($resultado['data']['cnpj_basico']) && !empty($resultado['data']['cnpj_basico'])) {
      $documento = $resultado['data']['cnpj_basico'];
    } else {
      $documento = null;
    }

    if($nome == false && $documento == false){
      $nome = null;
      $documento = $parametros['valor'];
    }

    $documento = preg_replace('/[^0-9]/', '', $parametros['valor']);
    $documento_formatado = substr_replace($documento, '.', 2, 0);
    $documento_formatado = substr_replace($documento_formatado, '.', 6, 0);
    $documento_formatado = substr_replace($documento_formatado, '/', 10, 0);
    $documento_formatado = substr_replace($documento_formatado, '-', 15, 0);

  }
  
  $conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#nome_contribuinte', ['class' => 'skip-main']);
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


  if($tipo_contribuinte == 'PESSOA JURIDICA'){
    $mensagem_erro_nome = 'Contribuinte não encontrado em nossas bases, retorne e verifique o documento consultado.';
  }else{
    $mensagem_erro_nome = 'Contribuinte não encontrado em nossas bases, digite o nome do contribuinte para continuar.';
  }

  //MENSAGENS D ERROS 
  echo $this->Html->tag('div',

      $this->Html->tag('div',
          $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
          $this->Html->tag('p', 'Verifique o formulário e preencha corretamente os campos', ['class' => 'texto-info-erro'])
      , ['class' => 'info-erro'])

  , ['id'=>'erro-nome','class' => 'col-sm-8 box-info-erro erro-cpf', 'style'=>'display:none']);

  if($nome == null){
      echo $this->Html->tag('div',

      $this->Html->tag('div',
          $this->Html->tag('i', 'report_problem', ['class' => 'material-icons icon-report_problem']).
          $this->Html->tag('p', $mensagem_erro_nome, ['class' => 'texto-info-receita'])
      , ['class' => 'info-impressao'])

  , ['class' => 'col-sm-8 box-info-receita']);
  }

  echo $this->Html->div('',
      $this->Form->create(null, [
          'url' => ['action' => 'solicitaCertidao'], 
          'type' => 'get', 
          'id' => 'form_solicitacao', 
          'onsubmit' => 'event.preventDefault(); onSubmit();'
      ]) .

  // entrada oculta para $tipo_contribuinte
  $this->Form->hidden('tipo_contribuinte', ['value' => $tipo_contribuinte]).



  // NOME
  $this->Html->div('',
      $this->Form->label('label_nome', 'Nome do contribuinte', ['class' => "label-cdf col-md-4 col-form-label", 'id' => 'label_nome_contribuinte']) .
      $this->Html->div('',
          $this->Form->control('nome_contribuinte', [
              'id' => 'nome_contribuinte',
              'class' => 'valor-mask form-control',
              'title' => "Nome do Contribuinte",
              'style' => 'padding-left:10px !important; margin-left: 10px !important;',
              'value' => !empty($nome) ? $nome : "",
              'label' => false,
              'readonly' => !empty($nome) || $tipo_contribuinte === 'PESSOA JURIDICA' ? true : false,
              'placeholder' => $tipo_contribuinte === 'PESSOA JURIDICA' ? 'NÃO ENCONTRADO.' : 'Digite aqui o nome do contribuinte.',
              'maxlength' => 400,
          ]),
      ['class' => 'col-md-8']),
      ['class' => 'form-group row', 'id' => 'input_nome_contribuinte']).

          //Mensagem validação EMAIL
          $this->Html->div('',
          '',
          ['class' => 'mensagem-nome', 'id' => 'mensagem-nome']).


      // DOCUMENTO
      $this->Html->div('',
          $this->Form->label('valor', 'Documento do contribuinte', ['class' => "label-cdf col-md-4 col-form-label", 'id' => 'label_valor']) .
          $this->Html->div('',
              $this->Form->control('valor', [
                  'id' => 'valor',
                  'class' => 'valor-mask form-control',
                  'placeholder' => 'Digite aqui o nº do documento',
                  'title' => "Digite o número do documento escolhido",
                  'style' => 'padding-left:10px !important; margin-left: 10px !important;',
                  'value' => $documento_formatado,
                  'readonly' => true,
                  'label' => false
              ]),
          ['class' => 'col-md-8']),
      ['class' => 'form-group row', 'id' => 'input_valor']) .

      // EMAIL
      $this->Html->div('',
          $this->Form->label('email_label', 'Email', ['class' => "label-cdf col-md-4 col-form-label", 'id' => 'label_email']) .
          $this->Html->div('',
              $this->Form->control('email', [
                  'id' => 'email',
                  'class' => 'valor-mask form-control',
                  'style' => 'padding-left:10px !important; margin-left: 10px !important;',
                  'value' => $email,
                  'readonly' => true,
                  'label' => false
              ]),
          ['class' => 'col-md-8']),
      ['class' => 'form-group row', 'id' => 'input_email']),

      ['class' => 'col-sm-4 grupo-form']);


      //Mudando o comportamento do botão avançar caso PESSOA JURIDICA

      $botaoAcao = ($tipo_contribuinte === "PESSOA JURIDICA" && $nome == null) ? "return false;" : "validateAndOpenModal();";
      $botaoClasse = "bt-grupo bt-buscar";
      $display = ($tipo_contribuinte === "PESSOA JURIDICA"  && $nome == null) ? 'display:none' : '';
      
      $botaoAtributos = [
          'type' => 'button',
          'class' => $botaoClasse,
          'title' => 'Avançar para resultado da consulta',
          'id' => 'btn-avancar',
          'onclick' => $botaoAcao,
          'style' => $display
      ];
      
      if ($tipo_contribuinte !== "PESSOA JURIDICA") {
          $botaoAtributos['data-toggle'] = 'modal';
          $botaoAtributos['data-target'] = '#exampleModalCenter';
      }

      
  // Botões do formulário
  echo $this->Html->tag('div',
      $this->Html->tag('span',
          $this->Form->button('Voltar', ['type' => '', 'class' => 'bt-grupo bt-voltar', 'title'=>'Voltar', 'onclick' => 'return false;'])
      ,['class' => 'bts', 'style' => 'float:left; display: none;']).

      $this->Html->tag('span',
          $this->Form->button('Avançar', $botaoAtributos),
          ['class' => 'bts', 'style' => 'float:right']
      ).

      $this->Html->tag('span',
      $this->Html->link('Voltar', ['controller' => 'Solicitacao', 'action' => 'index'], [
          'class' => 'bt-grupo bt-voltar',
          'title' => 'Voltar'
      ]),
      ['class' => 'bts', 'style' => 'float:right']
  )
      
  , ['class' => 'bt-group col-sm-4']);

  echo $this->Form->end();


?>

  <!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Aviso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fechar-modal" aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Deseja prosseguir com a solicitação de Certidão de Regularidade Fiscal para o contribuinte <strong id="nome_nomeado"></strong> com documento <strong><?php echo $documento_formatado ?></strong>&nbsp?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary bt-nao" data-dismiss="modal">Não</button>
                <button type="button" class="btn btn-primary bt-sim" id="btn-sim">Sim</button>
            </div>
        </div>
    </div>
</div>




<script>

document.addEventListener('DOMContentLoaded', function () {
    const inputNomeContribuinte = document.getElementById('nome_contribuinte');
    const maxLength = 400;

    inputNomeContribuinte.addEventListener('input', function () {
        if (inputNomeContribuinte.value.length > maxLength) {
            inputNomeContribuinte.value = inputNomeContribuinte.value.slice(0, maxLength);
        }
    });
});


  if (<?php echo json_encode($nome); ?> !== null) {
    // Obtém a referência da div da mensagem sobre erro do nome
    var mensagemNome = document.getElementById('mensagem-nome');
    // Esconde a div
    mensagemNome.style.display = 'none';
  }

  document.getElementById('btn-sim').addEventListener('click', function() {
      document.getElementById('form_solicitacao').submit();    
  });

  document.getElementById('btn-avancar').addEventListener('click', function(event) {

          // Obtém os valores dos campos relevantes
      var nome_contribuinte = document.getElementById("nome_contribuinte").value;
      var mensagemNome = document.getElementById('mensagem-nome');

      // Verifica as condições de validação
      if (mensagemNome.innerHTML === 'Nome válido.') {

        var divErroNome = document.getElementById("erro-nome");
        divErroNome.style.display = "none";

        // Atualiza o valor do elemento na modal
        document.getElementById("nome_nomeado").textContent = nome_contribuinte;

          
        // Abre a modal apenas se a condição for atendida
        $('#exampleModalCenter').modal('show');

      }else{

        // Exibe a div de erro
        var divErroNome = document.getElementById("erro-nome");
        divErroNome.style.display = "block";
        event.stopPropagation(); // Impede a propagação do evento de clique
        event.preventDefault(); // Impede a ação padrão do botão

      }
  });

  function validarNome(nome) {

    // Verifica se o nome possui espaço em branco
    if (!/\s/.test(nome)) {
      return false;
    }

    // Divide o nome em nome e sobrenome(s)
    var partesNome = nome.split(' ');
    var primeiroNome = partesNome[0];
    var sobrenome = partesNome.slice(1).join(' ');

    // Verifica se o primeiro nome possui pelo menos 3 caracteres
    if (primeiroNome.length < 2) {
      return false;
    }

    // Converte todas as letras do primeiro nome para minúsculas
    primeiroNome = primeiroNome.toLowerCase();

    // Verifica se o primeiro nome não possui mais de 3 caracteres repetidos consecutivos
    for (var i = 0; i < primeiroNome.length - 2; i++) {
      if (primeiroNome[i] === primeiroNome[i + 1] && primeiroNome[i] === primeiroNome[i + 2]) {
        return false;
      }
    }

    // Verifica se o primeiro nome contém apenas letras, incluindo acentuação e o caractere "ç"
    var letrasRegex = /^[a-záàâãéèêíïóôõöúç]+$/;
    if (!letrasRegex.test(primeiroNome)) {
      return false;
    }

    // Verifica se cada sobrenome possui pelo menos 1 caracteres
    var sobrenomes = sobrenome.split(' ');
    for (var j = 0; j < sobrenomes.length; j++) {
      var sobrenomeAtual = sobrenomes[j];
      if (sobrenomeAtual.length < 1) {
        return false;
      }

      // Converte todas as letras do sobrenome para minúsculas
      sobrenomeAtual = sobrenomeAtual.toLowerCase();

      // Verifica se cada sobrenome não possui mais de 3 caracteres repetidos consecutivos
      for (var k = 0; k < sobrenomeAtual.length - 2; k++) {
        if (
          sobrenomeAtual[k] === sobrenomeAtual[k + 1] &&
          sobrenomeAtual[k] === sobrenomeAtual[k + 2]
        ) {
          return false;
        }
      }

      // Verifica se cada sobrenome contém apenas letras, incluindo acentuação e o caractere "ç"
      if (!letrasRegex.test(sobrenomeAtual)) {
        return false;
      }
    }

    return true;
}



function exibirMensagemValidacaoNome(nome) {
  
  var nomeInput = document.getElementById('nome_contribuinte');
  var mensagemNome = document.getElementById('mensagem-nome');

  var tipoContribuinte = <?php echo json_encode($tipo_contribuinte); ?>;

  if(tipoContribuinte === 'PESSOA FISICA'){

    if (nome.length === 0) {
      mensagemNome.innerHTML = '';
    } else if (validarNome(nome)) {
      mensagemNome.innerHTML = 'Nome válido.';
      mensagemNome.style.color = 'green';
    } else {
      mensagemNome.innerHTML = 'Nome inválido. Digite seu nome completo.';
      mensagemNome.style.color = 'red';
    }

  }else{
    var mensagemNome = document.getElementById('mensagem-nome');
    mensagemNome.style.display = 'none';
    mensagemNome.innerHTML = 'Nome válido.';
    mensagemNome.style.color = 'green';
  }

  
}

var nomeInput = document.getElementById('nome_contribuinte');

nomeInput.addEventListener('input', function() {
  exibirMensagemValidacaoNome(this.value);
});

document.addEventListener('DOMContentLoaded', function() {
  exibirMensagemValidacaoNome(nomeInput.value);
});

</script>








