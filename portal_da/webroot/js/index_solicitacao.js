document.addEventListener('DOMContentLoaded', function() {

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
          var salvaMascaraCpf = localStorage.getItem('mask');
          if (salvaMascaraCpf) {
            valor.mask(salvaMascaraCpf);
          }
      
          // Aplica a máscara correspondente à opção selecionada
          switch ($(this).val()) {
            case 'Pessoa Física':
              valor.mask('000.000.000-00');
              break;
            case 'Pessoa Jurídica':
              valor.mask('00.000.000/0000-00');
              break;
            default:
              valor.unmask(); // Remove a máscara apenas para outras opções
          }
        });
      
        // Verifica o campo atual e completa com zeros à esquerda aplicando máscara
        valor.on('blur', function() {

            if ($('#campo').val() === 'Pessoa Física' && valor.val().length < 14 && valor.val().length > 0) {
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(11, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); // Aplica a máscara de CPF
                valor.val(maskedValue);

            }else if($('#campo').val() === 'Pessoa Jurídica' && valor.val().length < 18 && valor.val().length > 0){
                var currentValue = valor.val().replace(/\D/g, ''); // Remove os caracteres não numéricos
                var paddedValue = currentValue.padStart(14, '0'); // Completa com zeros à esquerda se necessário
                var maskedValue = paddedValue.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'); // Aplica a máscara de CNPJ
                valor.val(maskedValue);

            }
        });
      
        // Verifica se há um estado salvo da máscara e aplica-o
        var salvaMascaraCpf = localStorage.getItem('mask');
        if (salvaMascaraCpf) {
          valor.mask(salvaMascaraCpf);
        }
      
        // Salva o estado da máscara antes de enviar o formulário
        $(window).on('beforeunload', function() {
          var currentMask = valor.data('mask');
          localStorage.setItem('mask', currentMask);
        });
      
        // Restaura o estado da máscara ao carregar a página novamente
        $(window).on('load', function() {
          var salvaMascaraCpf = localStorage.getItem('mask');
          if (salvaMascaraCpf) {
            valor.mask(salvaMascaraCpf);
          }
        });
      });

});