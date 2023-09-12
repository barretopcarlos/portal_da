document.addEventListener('DOMContentLoaded', function() {

    $(window).on("load", function() {

        $('#opcao_doc').hide();
        $('#numero-doc').hide();
        $('#tipo-doc').hide();
        $('#sel-municipio').hide();
        $('#sel-cap').hide();
        $('#sel-municipio').hide();
        
        var selectedOptionText = $('#campo option:selected').text();
            
        if(selectedOptionText == "Selecione uma opção"){
            $('label[for="valor"]').text("");
            $
        }else{
            if(selectedOptionText == "Taxa de Incêndio"){
                $('label[for="valor"]').text("Inscrição predial");
            }else{
                $('label[for="valor"]').text(selectedOptionText);
            }
        }

        $campo = document.getElementById("campo").value;
        $campo_pa = document.getElementById("campo_pa").value;
        
        if (document.getElementById("campo").value == "CNPJ") {

            $('#cnpj-raiz').show();
            $('#label_valor').show();

        }else if(document.getElementById("campo").value == "Taxa de Incêndio"){

            $('#municipios').show();
            $('#label_valor').show();

        }else if(document.getElementById("campo").value == "Processo Administrativo"){
            
            $('#input_valor_pa').show();
            $('#valor').hide();
            $('#label_valor').hide();
            $('#campo_pa').val('E-');
            $('#valor_pa_orgao').val('');
            $('#valor_pa_unidade_protocoladora').val('');
            $('#valor_pa_processo').val('');
            $('#valor_pa_ano').val('');
        
        }
        
        if (document.getElementById("campo").value == "Selecione uma opção") {
            document.getElementById("valor").disabled = true;
        }else{
            document.getElementById("valor").disabled = false;
        }

    });

    var valor;

    $(document).ready(function() {
        var valor = $('#valor'); // Captura o input de id = valor
      
        if (document.getElementById("campo").value == "Selecione uma opção") {
          valor.attr('placeholder', '');
        } else {
          valor.attr('placeholder', 'Digite aqui o nº do documento');
        }
      
        // Adiciona o evento change no input de id = campo
        $('#campo').on('change', function() {
          valor.val(''); // Limpa o valor atual do input
      
          // Verifica se há um estado salvo da máscara e aplica-o
          var savedMask = localStorage.getItem('mask');
          if (savedMask) {
            valor.mask(savedMask);
          }
      
          // Aplica a máscara correspondente à opção selecionada
          switch ($(this).val()) {
            case 'CPF':
              valor.mask('000.000.000-00');
              break;
            case 'CNPJ':
              valor.mask('00.000.000/0000-00');
              break;
            case 'Certidão':
              valor.mask('0000/000.000-0');
              break;
            case 'Taxa de Incêndio':
              valor.mask('00000000000', {
                reverse: true
              });
              break;
            case 'RENAVAM':
              valor.mask('0000000000-0');
              break;
            case 'Processo Judicial novo':
              valor.mask('0000000-00.0000.0.00.0000');
              break;
            case 'Processo Judicial antigo':
              valor.mask('0000.000.000000-0');
              break;
            case 'Auto de Infração':
              valor.mask('00000000000000');
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

            if ($('#campo').val() === 'CPF' && valor.val().length < 14 && valor.val().length > 0) {
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(11, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); // Aplica a máscara de CPF
                valor.val(maskedValue);

            }else if($('#campo').val() === 'CNPJ' && valor.val().length < 18 && valor.val().length > 0){
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(14, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'); // Aplica a máscara de CNPJ
                valor.val(maskedValue);

            }else if($('#campo').val() === 'RENAVAM' && valor.val().length < 12 && valor.val().length > 0){
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(11, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{10})(\d{1})/, '$1-$2'); // Aplica a máscara de RENAVAM
                valor.val(maskedValue);

            }
        });
      
        // Verifica se há um estado salvo da máscara e aplica-o
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
      });
      
      
        var opcaoDropdown = document.getElementById("campo");
        var valorInput = document.getElementById("valor");
        var valorInputPa = document.getElementsByClassName("valor_pa");

        // Desabilitar input quando não houver opção selecionada.
        opcaoDropdown.addEventListener("change", function() {
            
            if (opcaoDropdown.value == "Selecione uma opção") {
                valorInput.value = "";
                valorInput.disabled = true;
                valorInput.readOnly = true;
                valorInput.placeholder = "";
            } else {
                $('#label_valor').show();
                valorInput.style.display = "";
                valorInput.disabled = false;
                valorInput.readOnly = false;
                document.getElementById("valor").placeholder = "Digite aqui o nº do documento";
                
            }
        });


        // Comportamento botão Limpar para todos os elementos do form
        var limparBotao = document.querySelector('.bt-limpar');
        limparBotao.addEventListener('click', function() {
            var valorInput = document.getElementById('valor');
            valorInput.value = "";
            valorInput.disabled = true;
            $('#valor').show();
            $('#label_valor').show();
            $('label[for="valor"]').text("");
            $('#cnpj-raiz').hide();
            $('.acordeon').hide();
            $('#municipios').hide();
            $('#input_valor_pa').hide();
            $('#input_valor').show();

            $('#opcao_doc').hide();
            $('#numero-doc').hide();
            $('#tipo-doc').hide();
            $('#sel-municipio').hide();
            $('#sel-cap').hide();
            $('#sel-municipio').hide();
            
            document.getElementById("valor").placeholder = "";

            grecaptcha.reset();

        });


        // Impedir envio do form com valor vazio.
        document.getElementById('form_consulta').addEventListener('submit', function(event) {

            const select = document.getElementById('campo');
            const input = document.getElementById('valor');
            const input_campo_pa = document.getElementById('campo_pa');
            const input_orgao = document.getElementById('valor_pa_orgao');
            const input_unidade = document.getElementById('valor_pa_unidade_protocoladora');
            const input_processo = document.getElementById('valor_pa_processo');
            const input_ano = document.getElementById('valor_pa_ano');
            const select_municipio = document.querySelector('#cidade_logradouro');

            if(select.value != 'Processo Administrativo'){
                
                if (select.value === 'Selecione uma opção' && input.value.trim() === '') {

                    $('#opcao_doc').show();
                    $('#numero-doc').hide();
                    $('#tipo-doc').hide();
                    $('#sel-cap').hide();
                    $('#sel-municipio').hide();
                    event.preventDefault();

                } else if(input.value.trim() === ''){

                    $('#opcao_doc').hide();
                    $('#numero-doc').show();
                    $('#tipo-doc').hide();
                    $('#sel-cap').hide();
                    $('#sel-municipio').hide();
                    event.preventDefault();

                } else if(select.value === 'Selecione uma opção' && input.value.trim() !== ''){

                    $('#opcao_doc').hide();
                    $('#numero-doc').hide();
                    $('#tipo-doc').show();
                    $('#sel-cap').hide();
                    $('#sel-municipio').hide();
                    event.preventDefault();
                // Impedir da consutla por tx de incêndio ir sem municipio selecionado.
                } else if(select.value === 'Taxa de Incêndio' && select_municipio.selectedIndex == 0){                   
                       
                        $('#opcao_doc').hide();
                        $('#numero-doc').hide();
                        $('#tipo-doc').hide();
                        $('#sel-cap').hide();
                        $('#sel-municipio').show();
                        event.preventDefault();  

                } else {
                    
                    // Obtém o valor do token reCAPTCHA
                    var recaptchaResponse = document.getElementById('g-recaptcha-response').value;
                    if (recaptchaResponse === null || recaptchaResponse === '') {
                        //if (!msgExibida) {
                            $('#opcao_doc').hide();
                            $('#numero-doc').hide();
                            $('#tipo-doc').hide();
                            $('#sel-cap').show();
                            $('#sel-municipio').hide();
                        //    msgExibida = true;
                        //}
                    } else {
                        // Adiciona o valor do token como um campo oculto no formulário
                        var form = document.getElementById('form_consulta');
                        var input_cap = document.createElement('input');
                        input_cap.type = 'hidden';
                        input_cap.name = 'g-recaptcha-response';
                        input_cap.value = recaptchaResponse;
                        form.appendChild(input_cap);
                        // Envia o formulário
                        form.submit();
                    }

                }

            }else{

                if (input_orgao == '' && input_unidade == '' && input_processo == '' && input_ano == '' ) {
                    
                    $('#opcao_doc').show();
                    $('#numero-doc').hide();
                    $('#tipo-doc').hide();
                    event.preventDefault();
                    
                }else{

                    // Obtém o valor do token reCAPTCHA
                    var recaptchaResponse = document.getElementById('g-recaptcha-response').value;
                    if (recaptchaResponse === null || recaptchaResponse === '') {
                        
                        $('#opcao_doc').hide();
                        $('#numero-doc').hide();
                        $('#tipo-doc').hide();
                        $('#sel-cap').show();
                        $('#sel-municipio').hide();
                                        
                    } else {
                        // Adiciona o valor do token como um campo oculto no formulário
                        var form = document.getElementById('form_consulta');
                        var input_cap = document.createElement('input');
                        input_cap.type = 'hidden';
                        input_cap.name = 'g-recaptcha-response';
                        input_cap.value = recaptchaResponse;
                        form.appendChild(input_cap);
                        // Envia o formulário
                        form.submit();
                    }
                }
            }   
        });


        // Mostrar/ocultar checkbox caso opçpão seja CNPJ
        $('#campo').on('change', function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'CNPJ') {
                $('#cnpj-raiz').show();
            } else {
                $('#cnpj-raiz').hide();
            }
        });

        // Mostrar/ocultar municipios caso opçpão seja Taxa de Incêndio
        $('#campo').on('change', function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'Taxa de Incêndio') {
                $('#municipios').show();
            } else {
                $('#municipios').hide();
            }
        });


        // Mostrar/ocultar dica de preenchimento caso opçpão seja PA
        $('#campo').on('change', function() {
            var selectedOption = $(this).val();
            if (selectedOption === 'Processo Administrativo') {

                $('.acordeon').show();
                $('#valor_pa_orgao').val('');
                $('#valor_pa_unidade_protocoladora').val('');
                $('#valor_pa_processo').val('');
                $('#valor_pa_ano').val('');

                $('#input_valor_pa').show();
                $('#input_valor').hide();  
            } else {
                $('.acordeon').hide();
                $('#input_valor_pa').hide();
                $('#input_valor').show();
            }
        });

        // Comportamento do acordeon 
        $(document).ready(function(){
            $('.acordeon-conteudo').hide(); // oculta todos os conteúdos de acordeon
            $('.acordeon-titulo').click(function(){
            $(this).next('.acordeon-conteudo').slideToggle(); // exibe/oculta o conteúdo ao clicar no título
            });
        });

        // Altera label de input ao selecionar tipo 
        $('#campo').on('change', function() {
            var selectedOptionText = $('#campo option:selected').text();
            
            if(selectedOptionText == "Selecione uma opção"){
                $('label[for="valor"]').text("");
                $
            }else{
                if(selectedOptionText == "Taxa de Incêndio"){
                    $('label[for="valor"]').text("Inscrição predial");
                }else{
                    $('label[for="valor"]').text(selectedOptionText);
                }
            }
        });
        


        $(document).ready(function() {
            // armazena as opções do dropdown
            var options = {
                'E-': {
                    tipo: 'e-',
                    orgao: '2',
                    unidade_protocoladora: '3',
                    processo: '6',
                    ano: '4'
                },
                'E-66': {
                    tipo: '66',
                    orgao: null,
                    unidade_protocoladora: null,
                    processo: '11',
                    ano: '4'
                },
                'E-77': {
                    tipo: '77',
                    orgao: null,
                    unidade_protocoladora: null,
                    processo: '11',
                    ano: '4'
                },
                'E-88': {
                    tipo: '88',
                    orgao: null,
                    unidade_protocoladora: null,
                    processo: '11',
                    ano: '4'
                },
                'E-99': {
                    tipo: '99',
                    orgao: null,
                    unidade_protocoladora: null,
                    processo: '11',
                    ano: '4'
                },
                'SEI-': {
                    tipo: 'sei',
                    orgao: null,
                    unidade_protocoladora: '6',
                    processo: '6',
                    ano: '4'
                },
                'IPS': {
                    tipo: 'ips',
                    orgao: null,
                    unidade_protocoladora: null,
                    processo: '6',
                    ano: '4'
                }
            };

            // função para aplicar as regras
            function aplicarRegras() {
                var selectedOption = $('#campo_pa').val();
                var selectedOptionRules = options[selectedOption];
                
                // limpa os inputs
                $('.valor_pa').val('');

                // mostra todos os inputs
                $('.valor_pa').show();
                $('.valor_pa').removeClass('hidden');

                // esconde os inputs
                if (selectedOptionRules.tipo === 'e-') {
                    $('#valor_pa_orgao').val('');
                    $('#valor_pa_unidade_protocoladora').val('');
                    $('#valor_pa_processo').val('');
                    $('#valor_pa_ano').val('');

                    $('#valor_pa_orgao').show();
                    $('#valor_pa_unidade_protocoladora').show();
                    $('#valor_pa_processo').show();
                    $('#valor_pa_ano').show();

                    $('#sep1').show();
                    $('#sep2').show();

                    $('#valor_pa_orgao').removeClass('hidden');
                    $('#valor_pa_unidade_protocoladora').removeClass('hidden');
                    $('#valor_pa_processo').removeClass('hidden');
                    $('#valor_pa_ano').removeClass('hidden');
                    
                }else if(selectedOptionRules.tipo === '66' || selectedOptionRules.tipo === '77' || selectedOptionRules.tipo === '88' || selectedOptionRules.tipo === '99') {

                    $('#valor_pa_orgao').val('');
                    $('#valor_pa_unidade_protocoladora').val('');
                    $('#valor_pa_processo').val('');
                    $('#valor_pa_ano').val('');

                    $('#valor_pa_orgao').hide();
                    $('#valor_pa_unidade_protocoladora').hide();
                    $('#valor_pa_processo').show();
                    $('#valor_pa_ano').show();

                    $('#sep1').hide();
                    $('#sep2').hide();

                    $('#valor_pa_orgao').addClass('hidden');
                    $('#valor_pa_unidade_protocoladora').addClass('hidden');
                    $('#valor_pa_processo').removeClass('hidden');
                    $('#valor_pa_ano').removeClass('hidden');
                    
                }else if(selectedOptionRules.tipo === 'sei'){

                    $('#valor_pa_orgao').val('');
                    $('#valor_pa_unidade_protocoladora').val('');
                    $('#valor_pa_processo').val('');
                    $('#valor_pa_ano').val('');

                    $('#valor_pa_orgao').hide();
                    $('#valor_pa_unidade_protocoladora').show();
                    $('#valor_pa_processo').show();
                    $('#valor_pa_ano').show();

                    $('#sep1').hide();
                    $('#sep2').show();

                    $('#valor_pa_orgao').addClass('hidden');
                    $('#valor_pa_unidade_protocoladora').removeClass('hidden');
                    $('#valor_pa_processo').removeClass('hidden');
                    $('#valor_pa_ano').removeClass('hidden');

                }else if (selectedOptionRules.tipo === 'ips'){

                    $('#valor_pa_orgao').val('');
                    $('#valor_pa_unidade_protocoladora').val('');
                    $('#valor_pa_processo').val('');
                    $('#valor_pa_ano').val('');

                    $('#valor_pa_orgao').hide();
                    $('#valor_pa_unidade_protocoladora').hide();
                    $('#valor_pa_processo').show();
                    $('#valor_pa_ano').show();

                    $('#sep1').hide();
                    $('#sep2').hide();

                    $('#valor_pa_orgao').addClass('hidden');
                    $('#valor_pa_unidade_protocoladora').addClass('hidden');
                    $('#valor_pa_processo').removeClass('hidden');
                    $('#valor_pa_ano').removeClass('hidden');

                }

                

                // define a máscara dos inputs
                $('#valor_pa_orgao').attr('maxlength', selectedOptionRules.orgao);
                $('#valor_pa_unidade_protocoladora').attr('maxlength', selectedOptionRules.unidade_protocoladora);
                $('#valor_pa_processo').attr('maxlength', selectedOptionRules.processo);
                $('#valor_pa_ano').attr('maxlength', selectedOptionRules.ano);

            }

            // chama a função no carregamento da página
            aplicarRegras();

            $('#campo_pa').change(function() {
                aplicarRegras();
            });
        });
        

});