<?php

$conteudo_principal = $this->Html->link('Ir para conteúdo principal', '#tabela_cnpj', ['class' => 'skip-main']);
echo $conteudo_principal;

$campo_json = json_encode($campo);

echo $this->Html->css(['consultaDebitosFiscais/consulta_debitos_fiscais']);
//echo $this->Html->css('jquery.dataTables.min');
echo $this->Html->css('cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css');

echo $this->Html->script('jquery-3.6.4.min');
echo $this->Html->css(['consultaDebitosFiscais/index']);
echo $this->Html->script('jquery.dataTables.min'); 

//modificar!
echo $this->Html->css(['https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css']);


// Exemplo BreadCrumb - Alterar quando tiver home do site
echo $this->Html->tag('div',
    $this->Html->tag('span', 'Home', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']).
    $this->Html->tag('span', 'Portal do contribuinte', ['class' => 'span-ba']).
    $this->Html->tag('span', '•', ['class' => 'separador-span']) .
    $this->Html->tag('span', 'Consulta Valores de débitos', ['class' => 'span-bb']), ['class' => 'exemplo_breadcrumb no-print']);

//Titulo
echo $this->Html->tag('h1', 'Consulta de valores de débitos', ['title' => 'Consulta de Valores de débitos']).
$this->Html->tag('br'),
$this->Html->tag('h2', 'Consulte os valores de débitos inscritos em Dívida Ativa por uma das opções disponíveis.', ['title' => 'Consulte os valores de débitos inscritos em Dívida Ativa por uma das opções disponíveis.','id'=>'conteudo-principal']);


$cadastros = json_decode($cadastros, true);

if($cadastros !== null){

    foreach ($cadastros['data'] as &$cadastro) {

        if (
            $cadastro['data_cancelamento'] === null &&
            ($cadastro['codigo_movimento'] === '5' || $cadastro['codigo_movimento'] === '6' || $cadastro['codigo_movimento'] === 'F' || $cadastro['codigo_movimento'] === 'T' || $cadastro['codigo_movimento'] === 'V')
        ) {
            $cadastro['mensagem_cda'] = "Em parcelamento. Entre em contato com parcelamento.pda@pge.rj.gov.br";
        } elseif (
            $cadastro['data_cancelamento'] === null &&
            ($cadastro['codigo_movimento'] === '9' || $cadastro['codigo_movimento'] === 'I' || $cadastro['codigo_movimento'] === 'J' || $cadastro['codigo_movimento'] === 'K' || $cadastro['codigo_movimento'] === 'L' || $cadastro['codigo_movimento'] === 'M' || $cadastro['codigo_movimento'] === 'N' || $cadastro['codigo_movimento'] === 'O' || $cadastro['codigo_movimento'] === 'P' || $cadastro['codigo_movimento'] === 'U' || $cadastro['codigo_movimento'] === 'W')
        ) {
            $cadastro['mensagem_cda'] = "Valores indisponíveis online. Entre em contato com atendimento.pda@pge.rj.gov.br";
        } else {
            $cadastro['mensagem_cda'] = null;
        }
    }
    
    $cadastros = json_encode($cadastros);

    //Condição para troca dados exibidos na tabela do resultado
    if($campo == 'CNPJ'){

        $valor = preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $valor);

        echo $this->Html->div(null,
            $this->Html->div('info-consulta',
                $this->Html->tag('p',
                    $campo . ':&nbsp;' . $this->Html->tag('span', $valor, ['class' => 'documento-consulta'])
                )
            ) .
            $this->Html->div('data_consulta',
                $this->Html->tag('p',
                    'Esta consulta tem como base a data de: ' . $this->Html->tag('span', h($data_consulta), ['class' => 'data'])
                ) .
                $this->Html->tag('i', 'local_printshop', [
                    'class' => 'material-icons icon-print no-print icon-print',
                    'title' => 'Imprimir Consulta',
                    'onclick' => 'window.print();'
                ])
            )
        );

        echo $this->Html->div('info-total',
            $this->Html->tag('p',
            '(*) No caso de certidões que estiverem ajuizadas, além deste valor, ainda incidirão as custas judiciais.'
            )
        );

        echo $this->Html->tag('table', '', ['id' => 'tabela_cnpj','class'=>'display responsive', 'style'=>'width:100%']);
        echo $this->Html->tag('thead', '');
        echo $this->Html->tag('tbody', '');

    }elseif($campo == 'CPF'){

        // aplicando máscara no CPF 
        $valor_cpf = preg_replace('/[^0-9]/', '', $valor);

        if (strlen($valor_cpf) < 11) {
            $valor_cpf = str_pad($valor_cpf, 11, '0', STR_PAD_LEFT);
        }

        $valor_cpf = substr_replace($valor_cpf, '.', 3, 0);
        $valor_cpf = substr_replace($valor_cpf, '.', 7, 0);
        $valor_cpf = substr_replace($valor_cpf, '-', 11, 0);


        echo $this->Html->div(null,
            $this->Html->div('info-consulta',
                $this->Html->tag('p',
                    $campo . ':&nbsp;' . $this->Html->tag('span', $valor_cpf, ['class' => 'documento-consulta'])
                )
            ) .
            $this->Html->div('data_consulta',
                $this->Html->tag('p',
                    'Esta consulta tem como base a data de: ' . $this->Html->tag('span', h($data_consulta), ['class' => 'data'])
                ) .
                $this->Html->tag('i', 'local_printshop', [
                    'class' => 'material-icons icon-print no-print icon-print',
                    'title' => 'Imprimir Consulta',
                    'onclick' => 'window.print();'
                ])
            )
        );

        echo $this->Html->div('info-total',
            $this->Html->tag('p',
            '(*) No caso de certidões que estiverem ajuizadas, além deste valor, ainda incidirão as custas judiciais.'
            )
        );

        echo $this->Html->tag('table', '', ['id' => 'tabela_cpf']);
        echo $this->Html->tag('thead', '');
        echo $this->Html->tag('tbody', '');

    }else{

        //Condição para aplicar a formatação/mascara correta do numero de acordo com tipo
        if($campo == "Processo Administrativo"){
                    
            if($campo_pa == "E-"){

                $valor = $campo_pa . $valor_pa_orgao . '/' . $valor_pa_unidade_protocoladora . '/' . $valor_pa_processo . '/' . $valor_pa_ano;
                
            }else if($campo_pa == "E-66" || $campo_pa == "E-77" || $campo_pa == "E-88" || $campo_pa == "E-99" ){

                $valor = $campo_pa . '/' . $valor_pa_processo . '/' . $valor_pa_ano;
                
            }else if($campo_pa == "SEI-" ){

                $valor = $campo_pa . $valor_pa_unidade_protocoladora . '/' . $valor_pa_processo . '/' . $valor_pa_ano;

            }else if($campo_pa == "IPS" ){
                
                $valor = $campo_pa . '/' . $valor_pa_processo . '/' . $valor_pa_ano;

            }

        }else if($campo == "Processo Judicial novo"){

            $valor = sprintf("%07d-%02d.%04d.%d.%02d.%04d",
                substr($valor, 0, 7),
                substr($valor, 7, 2),
                substr($valor, 9, 4),
                substr($valor, 13, 1),
                substr($valor, 15, 2),
                substr($valor, 17, 4)
            );


        }else if($campo == "Processo Judicial antigo"){

            $valor = sprintf("%04d.%03d.%06d-%d",
                substr($valor, 0, 4),
                substr($valor, 4, 3),
                substr($valor, 7, 6),
                substr($valor, 13, 1)
            );


        }else if($campo == "Certidão"){

            $valor = substr($valor, 0, 4) . '/' . substr($valor, 4, 3) . '.' . substr($valor, 7, 3) . '-' . substr($valor, 10);

        }else if($campo == "RENAVAM"){

            $valor = substr($valor, 0, 10) . '-' . substr($valor, 10);

        }



        echo $this->Html->div(null,
            $this->Html->div('info-consulta',
                $this->Html->tag('p',
                    $campo . ':&nbsp;' . $this->Html->tag('span', $valor, ['class' => 'documento-consulta'])
                )
            ) .
            $this->Html->div('data_consulta',
                $this->Html->tag('p',
                    'Esta consulta tem como base a data de: ' . $this->Html->tag('span', h($data_consulta), ['class' => 'data'])
                ) .
                $this->Html->tag('i', 'local_printshop', [
                    'class' => 'material-icons icon-print no-print',
                    'title' => 'Imprimir Consulta',
                    'onclick' => 'window.print();'
                ])
            )
        );

        echo $this->Html->div('info-total',
            $this->Html->tag('p',
            '(*) No caso de certidões que estiverem ajuizadas, além deste valor, ainda incidirão as custas judiciais.'
            )
        );

        echo $this->Html->tag('table', '', ['id' => 'tabela_documentos']);
        echo $this->Html->tag('thead', '');
        echo $this->Html->tag('tbody', '');

    }

    echo $this->Html->link('Voltar', ['controller' => 'ConsultaDebitosFiscais', 'action' => 'index'], ['class' => 'btn bt-voltar no-print','title'=>'Voltar para Tela anterior']);


}else{
    
    echo $this->Html->tag('i', 'warning', [
        'class' => 'material-icons warning-icon',
    ]).
    
    $this->Html->tag('p', 
    $campo . ' não encontrado(a) ou não existem dívidas para este documento',['class'=>'mensagem-sem-divida']);

    echo $this->Html->link('Voltar', ['controller' => 'ConsultaDebitosFiscais', 'action' => 'index'], ['class' => 'btn bt-voltar no-print','title'=>'Voltar para Tela anterior']);

}


?>

 
<script>
        
    var cadastros = <?php echo $cadastros; ?>;
    var campo = <?php echo $campo_json; ?>;

    if(campo == 'CNPJ'){

        //altera tabela caso a busca seja feita por CNPJ
        $(document).ready(function() {
            // Use o DataTables para preencher a tabela 
            $('#tabela_cnpj').DataTable({

                responsive: true,                 
                data: cadastros.data,
                columns: [
                    { data: 'linha', title: '&nbsp&nbsp&nbsp' },
                    {
                        data: 'numero_documento_principal',
                        title: 'CNPJ',
                        render: function(data) {
                            var cnpj = data.toString().padStart(14, '0'); // Adiciona zeros à esquerda se necessário
                            cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5'); // Aplica a máscara de CNPJ
                            return cnpj;
                        },
                        className: 'text-center, nowrap'
                    },
                    { 
                        data: 'nome_devedor', title: 'Razão Social'
                    },
                    {
                        data: 'numero_cda',
                        title: 'Certidão',
                        render: function(data) {
                            var cda = data.substr(0, 4) + '/' + data.substr(4, 3) + '.' + data.substr(7, 3) + '-' + data.substr(10);
                            return cda;
                        },
                        className: 'text-center, nowrap'
                    },
                    {
                        data: 'mensagem_cda',
                        title: 'Situação',
                        render: function(data, type, row) {
                            if (data !== null) {
                                return data;
                            } else if (row.data_ajuizamento != null) {
                                return 'Ajuizada';
                            } else {
                                return 'Não Ajuizada';
                            }
                        },
                    },
                    {
                        data: 'natureza.nome',
                        title: 'Natureza',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var naturezaFormatada = toTitleCase(data);
                                return naturezaFormatada;
                            }
                        }
                    },
                    { 
                        data: 'localidade.municipio', title: 'Município',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var municipioFormatado = toTitleCase(data);
                                return municipioFormatado;
                            }
                        },
                        className: 'text-center,nowrap'                        
                    },
                    {
                        data: 'data_ultimo_calculo',
                        title: 'Data cálculo',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                if(data == null){
                                    return '-';
                                }else{
                                    var date = new Date(data);
                                    var day = String(date.getDate()).padStart(2, '0');
                                    var month = String(date.getMonth() + 1).padStart(2, '0');
                                    var year = date.getFullYear();
                                    return day + '/' + month + '/' + year;
                                }
                            }
                        },
                        className: 'text-right,nowrap'
                    },
                    {
                        data: 'processo_administrativo',
                        title: 'Proc. Adm.',
                        render: function(data, type, row) {
                            return (row.mensagem_cda !== null) ? '-' : data;
                        },
                        className: 'text-center,nowrap'
                    },
                    {
                        data: 'auto_infracao',
                        title: 'Auto Infração',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else if (data == null && row.mensagem_cda == null) {
                                return '-';
                            } else {
                                return data;
                            }
                        },
                        className: 'text-center,nowrap'
                    },
                    {
                        data: 'valor_total_moeda',
                        title: 'Total (R$) *&nbsp&nbsp',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var bonus = row.data_ajuizamento ? 0.1 : 0.05;
                                var soma = parseFloat(data) * (1 + bonus);
                                var somaFormatada = soma.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return somaFormatada;                                
                            }
                        }
                    },

                ]
            });
        });

    }else if(campo == 'CPF'){

        //altera tabela caso a busca seja feita por CPF
        $(document).ready(function() {
            // Use o DataTables para preencher a tabela
            $('#tabela_cpf').DataTable({
                responsive: true,

                data: cadastros.data,
                columns: [
                    { data: 'linha', title: '&nbsp&nbsp&nbsp' },
                    {
                        data: 'numero_documento_principal',
                        title: 'CPF',
                        render: function(data) {
                            // Verificar se o CPF tem menos de 11 dígitos
                            if (data.length < 11) {
                                var zeros = "00000000000"; // 11 zeros à esquerda
                                data = (zeros + data).slice(-11); // Adicionar zeros à esquerda e manter apenas os últimos 11 dígitos
                            }
                            
                            // Aplicar a máscara de CPF
                            var cpf = data.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
                            
                            return cpf;
                        },
                        className: 'text-center, nowrap'
                    },
                    
                    {
                        data: 'numero_cda',
                        title: 'Certidão',
                        render: function(data) {
                            var cda = data.substr(0, 4) + '/' + data.substr(4, 3) + '.' + data.substr(7, 3) + '-' + data.substr(10);
                            return cda;
                        },
                        className: 'text-center, nowrap'
                    },
                    {
                        data: 'mensagem_cda',
                        title: 'Situação',
                        render: function(data, type, row) {
                            if (data !== null) {
                                return data;
                            } else if (row.data_ajuizamento != null) {
                                return 'Ajuizada';
                            } else {
                                return 'Não Ajuizada';
                            }
                        },
                    },
                    {
                        data: 'natureza.nome',
                        title: 'Natureza',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var naturezaFormatada = toTitleCase(data);
                                return naturezaFormatada;
                            }
                        }
                    },
                    { 
                        data: 'localidade.municipio', title: 'Município',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var municipioFormatado = toTitleCase(data);
                                return municipioFormatado;
                            }
                        },
                        className: 'text-center'                        
                    },
                    {
                        data: 'data_ultimo_calculo',
                        title: 'Data cálculo',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                if(data == null){
                                    return '-';
                                }else{
                                    var date = new Date(data);
                                    var day = String(date.getDate()).padStart(2, '0');
                                    var month = String(date.getMonth() + 1).padStart(2, '0');
                                    var year = date.getFullYear();
                                    return day + '/' + month + '/' + year;
                                }
                            }
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'processo_administrativo',
                        title: 'Proc. Adm.',
                        render: function(data, type, row) {
                            return (row.mensagem_cda !== null) ? '-' : data;
                        },
                        className: 'text-center, nowrap'
                    },
                    {
                        data: 'auto_infracao',
                        title: 'Auto Infração',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else if (data == null && row.mensagem_cda == null) {
                                return '-';
                            } else {
                                return data;
                            }
                        },
                        className: 'text-center, nowrap'
                    },
                    {
                        data: 'valor_total_moeda',
                        title: 'Total (R$) *&nbsp&nbsp',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var bonus = row.data_ajuizamento ? 0.1 : 0.05;
                                var soma = parseFloat(data) * (1 + bonus);
                                var somaFormatada = soma.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return somaFormatada;
                            }
                        },
                        className: 'text-right-total'
                    },

                ]
            });
        });

    }else{

        //altera tabela para os outros documentos
        $(document).ready(function() {
            // Use o DataTables para preencher a tabela
            $('#tabela_documentos').DataTable({
                
                data: cadastros.data,
                columns: [
                    { data: 'linha', title: '&nbsp&nbsp&nbsp' },                        
                    {
                        data: 'numero_cda',
                        title: 'Certidão',
                        render: function(data) {
                            var cda = data.substr(0, 4) + '/' + data.substr(4, 3) + '.' + data.substr(7, 3) + '-' + data.substr(10);
                            return cda;
                        },
                        className: 'text-center, nowrap'
                    },
                    {
                        data: 'mensagem_cda',
                        title: 'Situação',
                        render: function(data, type, row) {
                            if (data !== null) {
                                return data;
                            } else if (row.data_ajuizamento != null) {
                                return 'Ajuizada';
                            } else {
                                return 'Não Ajuizada';
                            }
                        },
                    },
                    {
                        data: 'natureza.nome',
                        title: 'Natureza',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var naturezaFormatada = toTitleCase(data);
                                return naturezaFormatada;
                            }
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'data_ultimo_calculo',
                        title: 'Data cálculo',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                if(data == null){
                                    return '-';
                                }else{
                                    var date = new Date(data);
                                    var day = String(date.getDate()).padStart(2, '0');
                                    var month = String(date.getMonth() + 1).padStart(2, '0');
                                    var year = date.getFullYear();
                                    return day + '/' + month + '/' + year;
                                }
                            }
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'valor_total_moeda',
                        title: 'Débito (R$)',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var debitoFormatado = parseFloat(data).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return debitoFormatado;
                            }
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'valor_total_moeda',
                        title: 'Honorários (R$)',
                        render: function(data, type, row) {
                            var bonus = row.data_ajuizamento ? 0.1 : 0.05;
                            var honorarios = (row.mensagem_cda !== null) ? '-' : (parseFloat(data) * bonus);
                            var honorariosFormatados = honorarios.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            return honorariosFormatados;
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'valor_total_moeda',
                        title: 'Total (R$) *&nbsp&nbsp',
                        render: function(data, type, row) {
                            if (row.mensagem_cda !== null) {
                                return '-';
                            } else {
                                var bonus = row.data_ajuizamento ? 0.1 : 0.05;
                                var soma = parseFloat(data) * (1 + bonus);
                                var somaFormatada = soma.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return somaFormatada;                                
                            }
                        },
                        className: 'text-right'
                    },

                ]
            });
        });

    }

        
    function toTitleCase(str) {
        return str.toLowerCase().replace(/(?:^|\s)\w/g, function(match) {
            return match.toUpperCase();
        });
    }

</script>
