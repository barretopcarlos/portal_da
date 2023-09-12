<?php
// Caminho: src/Controller/Component/PdfComponent.php

declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use TCPDF;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;




class PdfComponent extends Component{

    public function gerarPdfCertidaoNegativa($content,$cod_autenticidade_certidao,$data, $hora){

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Remover a linha horizontal no início do PDF
        $pdf->SetHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->SetMargins(20, 10, 20, 10);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 5); // evitar sobreposição de conteúdo

        //ADICIONANDO LOGO PGE NO TOPO
        $logo_pge = WWW_ROOT . 'img/logo_pge.png';
        $logoPgeWidth = 50; // Largura desejada da outra imagem
        // Centralizar a outra imagem horizontalmente
        $logoPgeX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        // Definir a posição vertical da outra imagem
        $logoPgeY = 10; 
        $pdf->Image($logo_pge, $logoPgeX, $logoPgeY, $logoPgeWidth);


        //GERANDO QRCODE
        // Gerar QR Code como uma imagem PNG
        $tempImagePath = WWW_ROOT . 'img/qrcode_temp.png';
        $url = 'http://desenvda.in.pge.rj.gov.br/fsw_da/da_portal_contribuinte/portal_contribuinte/consulta-autenticidade?valor=' . urlencode($cod_autenticidade_certidao);
        $imageWidth = 30;


        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'imageBase64' => false,
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($url, $tempImagePath);

        // Carregar a imagem do QR Code gerada
        $qrCodeImage = imagecreatefrompng($tempImagePath);

        // Criar uma nova imagem com fundo branco
        $qrCodeWithBackground = imagecreatetruecolor(imagesx($qrCodeImage), imagesy($qrCodeImage));
        $white = imagecolorallocate($qrCodeWithBackground, 255, 255, 255);
        imagefill($qrCodeWithBackground, 0, 0, $white);

        // Copiar o QR Code para a nova imagem com fundo branco
        imagecopy($qrCodeWithBackground, $qrCodeImage, 0, 0, 0, 0, imagesx($qrCodeImage), imagesy($qrCodeImage));

        // Salvar a nova imagem com fundo branco
        imagepng($qrCodeWithBackground, $tempImagePath);

        // Calcular a coordenada X para centralizar o QR Code horizontalmente
        $qrCodeX = ($pdf->GetPageWidth() - $imageWidth) / 2;

        // Ajustar a coordenada Y para definir a posição vertical
        $qrCodeY = $pdf->GetPageHeight() - $imageWidth - 30; 

        // Adicionar o QR Code ao PDF
        $pdf->Image($tempImagePath, $qrCodeX, $qrCodeY, $imageWidth);

        // Remover a imagem temporária após adicioná-la ao PDF
        unlink($tempImagePath);

        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $imageWidth -5); // Reduzir o valor para diminuir o espaço entre a logo e o texto

        $pdf->writeHTML($content, true, false, true, false, '');


        // Ajustar a posição vertical para adicionar o rodapé
        $pdf->SetY($pdf->GetY() + 50); // Espaço entre o conteúdo e o rodapé
        $pdf->SetTextColor(200, 200, 200);

        // Adicionar o rodapé personalizado no final da página
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Código da Certidão: ' . $cod_autenticidade_certidao . ' | ' . 'Consulta realizada em: ' . $data . ' às ' . $hora . 'min                                          ' . "Página " . $pdf->getAliasNumPage() . " de " . $pdf->getAliasNbPages() , 0, 1, 'L');
        // Exibir o PDF na tela
        $pdf_string = $pdf->Output('', 'S');
    
        return $pdf_string;
    }


    public function gerarPdfCertidaoPositiva($content,$cod_autenticidade_certidao,$dividas,$envolvidos,$data, $hora){

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Remover a linha horizontal no início do PDF
        $pdf->SetHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->SetAutoPageBreak(true, 20); // evitar sobreposição de conteúdo
        
        //CRIAÇÃO DAS LAUDAS ////////////////////////////////////////////////////////////////////////////////
        // As laudas são criadas primeiro para poder pegar o conteudo de laudas criadas a atualziar o conteudo da primeira página, que é criada posteriormente.

        // Adicionar nova página
        $pdf->AddPage();
        $pdf->SetMargins(10, 30, 10, 5);

        if($dividas != null){  //Verificando se há envolvimento em dividas para exibir ou não a segunda tabela

            // Título da primeira tabela
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetY($pdf->GetY() + 10); // Ajusta a posição vertical
            $pdf->Cell(0, 10, 'Relatório de Débitos Localizados', 0, 1, 'L');

            // Cabeçalho da primeira tabela
            $pdf->SetFont('helvetica', 'B',9);
            $pdf->Cell(10, 10, '', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Número CDA', 1, 0, 'C');
            $pdf->Cell(60, 10, 'CPF/CNPJ', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Natureza do Débito', 1, 1, 'C');

            // Conteúdo da primeira tabela
            $pdf->SetFont('helvetica', '', 9);

            $linhaPrimeiraTabela = 1;  
        
            foreach ($dividas as $divida) {

                $cpf = $divida['numero_documento_principal'];
                $cpf= str_pad($cpf, 11, '0', STR_PAD_LEFT);
                
                $cpf_formatado = substr_replace($cpf, '.', 3, 0);
                $cpf_formatado = substr_replace($cpf_formatado, '.', 7, 0);
                $cpf_formatado = substr_replace($cpf_formatado, '-', 11, 0);
                $cda =  substr($divida['numero_cda'], 0, 4) . '/' . substr($divida['numero_cda'], 4, 3) . '.' . substr($divida['numero_cda'], 7, 3) . '-' . substr($divida['numero_cda'], 10);
                
                $pdf->Cell(10, 10, $linhaPrimeiraTabela, 1, 0, 'C');
                $pdf->Cell(60, 10, $cda, 1, 0, 'C');
                $pdf->Cell(60, 10, $cpf_formatado, 1, 0, 'C');
            
                // Truncar o texto da natureza do débito se necessário
                $maxCellWidth = 60; // Largura da célula
                $text = $divida['natureza']['nome'];
                if ($pdf->getStringWidth($text) > $maxCellWidth) {
                    $text = substr($text, 0, 35) . '...'; // Truncar o texto para caber na célula
                }
                
                $pdf->Cell(60, 10, $text, 1, 1, 'C');

                $linhaPrimeiraTabela++;
            }
        }

    
        if($envolvidos != null){  //Verificando se há envolvimento em dividas para exibir ou não a segunda tabela

            // Título da segunda tabela
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetY($pdf->GetY() + 2); // Ajuste a posição vertical
            $pdf->Cell(0, 10, 'Relatório de Débitos C/ Registro de Envolvimento ou Corresponsabilidade', 0, 1, 'L');

            // Cabeçalho da segunda tabela
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(10, 10, '', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Número CDA', 1, 0, 'C');
            $pdf->Cell(60, 10, 'CPF/CNPJ', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Natureza do Débito', 1, 1, 'C');

            // Conteúdo da segunda tabela
            $pdf->SetFont('helvetica', '', 9);

            $linhaSegundaTabela = 1;

            foreach ($envolvidos as $envolvido) {

                $cpf_envol = $envolvido['Envolvidos']['cnpj_cpf'];
                $cpf_envol = str_pad($cpf_envol, 11, '0', STR_PAD_LEFT);
                
                $cpf_formatado_envol = substr_replace($cpf_envol, '.', 3, 0);
                $cpf_formatado_envol = substr_replace($cpf_formatado_envol, '.', 7, 0);
                $cpf_formatado_envol = substr_replace($cpf_formatado_envol, '-', 11, 0);
                $cda =  substr($envolvido['Envolvidos']['numero_cda'], 0, 4) . '/' . substr($envolvido['Envolvidos']['numero_cda'], 4, 3) . '.' . substr($envolvido['Envolvidos']['numero_cda'], 7, 3) . '-' . substr($envolvido['Envolvidos']['numero_cda'], 10);
            
                // Definir o alinhamento para 'C' (centralizado) em todas as células da segunda tabela
                $pdf->Cell(10, 10, $linhaSegundaTabela, 1, 0, 'C');
                $pdf->Cell(60, 10, $cda, 1, 0, 'C');
                $pdf->Cell(60, 10, $cpf_formatado_envol, 1, 0, 'C');
            
                // Truncar o texto se necessário
                $maxCellWidth = 60; // Largura da célula
            
                $texto_natureza = $envolvido['Natureza']['nome'];
                if ($pdf->getStringWidth($texto_natureza) > $maxCellWidth) {
                    $texto_natureza = substr($texto_natureza, 0, 30) . '...'; // Truncar o texto para caber na célula
                }
            
                $pdf->Cell(60, 10, $texto_natureza, 1, 1, 'C');

                $linhaSegundaTabela++;
            }

        }

        // Obter o número total de páginas geradas
        $total_laudas = $pdf->getPage();
        $total_paginas = $total_laudas + 1;
        $total_laudas_str = strval($total_laudas); // Converter o número em uma string

        // Criar uma nova primeira página vazia
        $pdf->AddPage();
        $pdf->SetMargins(20, 10, 20, 0);
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetAutoPageBreak(true, 0); // evitar sobreposição de conteúdo

        //ADICIONANDO LOGO PGE NO TOPO
        $logo_pge = WWW_ROOT . 'img/logo_pge.png';
        $logoPgeWidth = 50; // Largura desejada da outra imagem
        // Centralizar a outra imagem horizontalmente
        $logoPgeX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        // Definir a posição vertical da outra imagem
        $logoPgeY = 5; 
        $pdf->Image($logo_pge, $logoPgeX, $logoPgeY, $logoPgeWidth);
        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $logoPgeWidth -70); // Reduzir o valor para diminuir o espaço entre a logo e o texto


        //GERANDO QRCODE
        // Gerar QR Code como uma imagem PNG
        $tempImagePath = WWW_ROOT . 'img/qrcode_temp.png';
        $url = 'http://desenvda.in.pge.rj.gov.br/fsw_da/da_portal_contribuinte/portal_contribuinte/consulta-autenticidade?valor=' . urlencode($cod_autenticidade_certidao);
        $imageWidth = 30;


        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'imageBase64' => false,
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($url, $tempImagePath);

        // Carregar a imagem do QR Code gerada
        $qrCodeImage = imagecreatefrompng($tempImagePath);

        // Criar uma nova imagem com fundo branco
        $qrCodeWithBackground = imagecreatetruecolor(imagesx($qrCodeImage), imagesy($qrCodeImage));
        $white = imagecolorallocate($qrCodeWithBackground, 255, 255, 255);
        imagefill($qrCodeWithBackground, 0, 0, $white);

        // Copiar o QR Code para a nova imagem com fundo branco
        imagecopy($qrCodeWithBackground, $qrCodeImage, 0, 0, 0, 0, imagesx($qrCodeImage), imagesy($qrCodeImage));

        // Salvar a nova imagem com fundo branco
        imagepng($qrCodeWithBackground, $tempImagePath);

        // Calcular a coordenada X para centralizar o QR Code horizontalmente
        $qrCodeX = ($pdf->GetPageWidth() - $imageWidth) / 2;

        // Ajustar a coordenada Y para definir a posição vertical
        $qrCodeY = $pdf->GetPageHeight() - $imageWidth - 35; 
        // Adicionar o QR Code ao PDF
        $pdf->Image($tempImagePath, $qrCodeX, $qrCodeY, $imageWidth);

        // Remover a imagem temporária após adicioná-la ao PDF
        unlink($tempImagePath);

        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $imageWidth - 5); // Reduzir o valor para diminuir o espaço entre a logo e o texto

        // Substituir [numero_laudas] pelo número total de páginas do conteudo da primeira página
        $content = str_replace('[numero_laudas]', $total_laudas_str, $content);

        $pdf->setCellPadding(0, 0, 0, 0);

        // Criando a primeira página 
        $pdf->writeHTML($content, true, false, true, false, '');

        //Movendo a primeira página que é criada por ultimo, para a primeira posição do PDF
        $pdf->movePage($total_paginas, 1);


        $total_paginas = $pdf->getPage();

        // Percorrer as páginas do PDF para adicionar o rodapé
        for ($pagina = 1; $pagina <= $total_paginas; $pagina++) {
            // Definir a página atual para adicionar o conteúdo
            $pdf->setPage($pagina);

            // Posicionar o cursor no final da página
            $pdf->SetY(-25); 

            // Adicionar o texto no final da página
            $pdf->SetTextColor(200,200,200);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 5, 'Código da Certidão: ' . $cod_autenticidade_certidao . ' | ' . 'Consulta realizada em: ' . $data . ' às ' . $hora . 'min                                                                   ' . "Página " . $pdf->getAliasNumPage() . " de " . $pdf->getAliasNbPages() , 0, 1, 'L');

        }

        //ADICIONANDO LOGO PGE 
        $logo_pge = WWW_ROOT . 'img/logo_pge.png';
        $logoPgeWidth = 40; // Largura desejada da outra imagem
        // Centralizar a outra imagem horizontalmente
        $logoPgeX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        // Definir a posição vertical da outra imagem
        $logoPgeY = 10; 
        $novaPosicaoX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        $novaPosicaoY = 10;

        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $imageWidth -5); // Reduzir o valor para diminuir o espaço entre a logo e o texto

        // Percorrer as páginas do PDF para adicionar a logo no topo
        for ($pagina = 2; $pagina <= $total_paginas; $pagina++) {
            // Definir a página atual para adicionar o conteúdo
            $pdf->setPage($pagina);

            // Verificar se é a primeira página
            if ($pagina === 1) {
                // Adicionar a logo no topo da primeira página
                $pdf->Image($logo_pge, $logoPgeX, $logoPgeY, $logoPgeWidth);
            } else {
                // Definir a posição da logo nas páginas subsequentes (caso seja diferente)
                $pdf->Image($logo_pge, $novaPosicaoX, $novaPosicaoY, $logoPgeWidth);
            }

        }

        // Exibir o PDF na tela
        $pdf_string = $pdf->Output('', 'S');

        return $pdf_string;
    }


    public function gerarPdfCertidaoPositivaPj($content,$cod_autenticidade_certidao,$dividas,$envolvidos,$dividas_correlacionadas,$data,$hora){

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Remover a linha horizontal no início do PDF
        $pdf->SetHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->SetAutoPageBreak(true, 20); // evitar sobreposição de conteúdo
    
        //CRIAÇÃO DAS LAUDAS ////////////////////////////////////////////////////////////////////////////////
        // As laudas são criadas primeiro para poder pegar o conteudo de laudas criadas a atualziar o conteudo da primeira página, que é criada posteriormente.

        // Adicionar nova página
        $pdf->AddPage();
        $pdf->SetMargins(10, 30, 10, 5);

        if($dividas != null){  //Verificando se há envolvimento em dividas para exibir ou não a segunda tabela

            // Título da primeira tabela
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 10, 'Relatório de Débitos Localizados', 0, 1, 'L');

            // Cabeçalho da primeira tabela
            $pdf->SetFont('helvetica', 'B',8);
            $pdf->Cell(10, 10, '', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Número CDA', 1, 0, 'C');
            $pdf->Cell(60, 10, 'CPF/CNPJ', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Natureza do Débito', 1, 1, 'C');

            // Conteúdo da primeira tabela
            $pdf->SetFont('helvetica', '', 8);

            $linhaPrimeiraTabela = 1;  
        
            foreach ($dividas as $divida) {

            
                $cnpj = preg_replace('/[^0-9]/', '', $divida['numero_documento_principal']);

                if (strlen($cnpj) < 14) {
                    $cnpj = str_pad($cnpj, 14, "0", STR_PAD_LEFT);
                }

                $cnpj_formatado = substr_replace($cnpj, '.', 2, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '.', 6, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '/', 10, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '-', 15, 0);

                $cda =  substr($divida['numero_cda'], 0, 4) . '/' . substr($divida['numero_cda'], 4, 3) . '.' . substr($divida['numero_cda'], 7, 3) . '-' . substr($divida['numero_cda'], 10);
                
                $pdf->Cell(10, 10, $linhaPrimeiraTabela, 1, 0, 'C');
                $pdf->Cell(60, 10, $cda, 1, 0, 'C');
                $pdf->Cell(60, 10, $cnpj_formatado, 1, 0, 'C');
            
                // Truncar o texto da natureza do débito se necessário
                $maxCellWidth = 60; // Largura da célula
                $text = $divida['natureza']['nome'];
                if ($pdf->getStringWidth($text) > $maxCellWidth) {
                    $text = substr($text, 0, 35) . '...'; // Truncar o texto para caber na célula
                }
                
                $pdf->Cell(60, 10, $text, 1, 1, 'C');

                $linhaPrimeiraTabela++;
            }
        }

    
        if($envolvidos != null){  //Verificando se há envolvimento em dividas para exibir ou não a segunda tabela

            // Título da segunda tabela
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 10, 'Relatório de Débitos C/ Registro de Envolvimento ou Corresponsabilidade', 0, 1, 'L');

            // Cabeçalho da segunda tabela
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(10, 10, '', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Número CDA', 1, 0, 'C');
            $pdf->Cell(60, 10, 'CPF/CNPJ', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Natureza do Débito', 1, 1, 'C');

            // Conteúdo da segunda tabela
            $pdf->SetFont('helvetica', '', 8);

            $linhaSegundaTabela = 1;

            foreach ($envolvidos as $envolvido) {

                $cnpj = preg_replace('/[^0-9]/', '', $envolvido['Envolvidos']['cnpj_cpf']);
                
                if (strlen($cnpj) < 14) {
                    $cnpj = str_pad($cnpj, 14, "0", STR_PAD_LEFT);
                }

                $cnpj_formatado = substr_replace($cnpj, '.', 2, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '.', 6, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '/', 10, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '-', 15, 0);

                $cda =  substr($envolvido['Envolvidos']['numero_cda'], 0, 4) . '/' . substr($envolvido['Envolvidos']['numero_cda'], 4, 3) . '.' . substr($envolvido['Envolvidos']['numero_cda'], 7, 3) . '-' . substr($envolvido['Envolvidos']['numero_cda'], 10);
            
                // Definir o alinhamento para 'C' (centralizado) em todas as células da segunda tabela
                $pdf->Cell(10, 10, $linhaSegundaTabela, 1, 0, 'C');
                $pdf->Cell(60, 10, $cda, 1, 0, 'C');
                $pdf->Cell(60, 10, $cnpj_formatado, 1, 0, 'C');
            
                // Truncar o texto se necessário
                $maxCellWidth = 60; // Largura da célula
            
                $texto_natureza = $envolvido['Natureza']['nome'];
                if ($pdf->getStringWidth($texto_natureza) > $maxCellWidth) {
                    $texto_natureza = substr($texto_natureza, 0, 30) . '...'; // Truncar o texto para caber na célula
                }
            
                $pdf->Cell(60, 10, $texto_natureza, 1, 1, 'C');

                $linhaSegundaTabela++;
            }

        }

        if($dividas_correlacionadas != null){  //Verificando se há envolvimento em dividas para exibir ou não a segunda tabela

            // Título da terceira tabela
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 10, 'Relatório de Débitos (Incorporações)', 0, 1, 'L');
            $pdf->Ln(5);

            // Cabeçalho da terceira tabela
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(10, 10, '', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Número CDA', 1, 0, 'C');
            $pdf->Cell(40, 10, 'CPF/CNPJ', 1, 0, 'C');
            $pdf->Cell(50, 10, 'Nome Devedor', 1, 0, 'C');
            $pdf->Cell(50, 10, 'Natureza do Débito', 1, 1, 'C');

            // Conteúdo da terceira tabela
            $pdf->SetFont('helvetica', '', 8);

            $linhaSegundaTabela = 1;

            foreach ($dividas_correlacionadas as $dividas_correlacionada) {

                $cnpj = preg_replace('/[^0-9]/', '', $dividas_correlacionada['numero_documento_principal']);
                
                if (strlen($cnpj) < 14) {
                    $cnpj = str_pad($cnpj, 14, "0", STR_PAD_LEFT);
                }

                $cnpj_formatado = substr_replace($cnpj, '.', 2, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '.', 6, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '/', 10, 0);
                $cnpj_formatado = substr_replace($cnpj_formatado, '-', 15, 0);

                $cda =  substr($dividas_correlacionada['numero_cda'], 0, 4) . '/' . substr($dividas_correlacionada['numero_cda'], 4, 3) . '.' . substr($dividas_correlacionada['numero_cda'], 7, 3) . '-' . substr($dividas_correlacionada['numero_cda'], 10);
                $nome_devedor = $dividas_correlacionada['nome_devedor'];

                // Truncar o texto se necessário
                $maxCellWidth = 50; // Largura da célula
            
                // Definir o alinhamento para 'C' (centralizado) em todas as células da segunda tabela
                $pdf->Cell(10, 10, $linhaSegundaTabela, 1, 0, 'C');
                $pdf->Cell(40, 10, $cda, 1, 0, 'C');
                $pdf->Cell(40, 10, $cnpj_formatado, 1, 0, 'C');

                if ($pdf->getStringWidth($nome_devedor) > $maxCellWidth) {
                    $nome_devedor = substr($nome_devedor, 0, 20) . '...'; // Truncar o texto para caber na célula
                }

                $pdf->Cell(50, 10, $nome_devedor, 1, 0, 'C');
            
                $texto_natureza = $dividas_correlacionada['natureza']['nome'];
                if ($pdf->getStringWidth($texto_natureza) > $maxCellWidth) {
                    $texto_natureza = substr($texto_natureza, 0, 30) . '...'; // Truncar o texto para caber na célula
                }
            
                $pdf->Cell(50, 10, $texto_natureza, 1, 1, 'C');

                $linhaSegundaTabela++;
            }

        }

        // Obter o número total de páginas geradas
        $total_laudas = $pdf->getPage();
        $total_paginas = $total_laudas + 1;
        $total_laudas_str = strval($total_laudas); // Converter o número em string

        // Criar uma nova primeira página vazia
        $pdf->AddPage();
        $pdf->SetMargins(20, 10, 20, 0);
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetAutoPageBreak(true, 0); // evitar sobreposição de conteúdo

        //ADICIONANDO LOGO PGE NO TOPO
        $logo_pge = WWW_ROOT . 'img/logo_pge.png';
        $logoPgeWidth = 50; // Largura desejada da outra imagem
        // Centralizar a outra imagem horizontalmente
        $logoPgeX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        // Definir a posição vertical da outra imagem
        $logoPgeY = 5; 
        $pdf->Image($logo_pge, $logoPgeX, $logoPgeY, $logoPgeWidth);
        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $logoPgeWidth -70); // Reduzir o valor para diminuir o espaço entre a logo e o texto


        //GERANDO QRCODE
        // Gerar QR Code como uma imagem PNG
        $tempImagePath = WWW_ROOT . 'img/qrcode_temp.png';
        $url = 'http://desenvda.in.pge.rj.gov.br/fsw_da/da_portal_contribuinte/portal_contribuinte/consulta-autenticidade?valor=' . urlencode($cod_autenticidade_certidao);
        $imageWidth = 30;


        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'imageBase64' => false,
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($url, $tempImagePath);

        // Carregar a imagem do QR Code gerada
        $qrCodeImage = imagecreatefrompng($tempImagePath);

        // Criar uma nova imagem com fundo branco
        $qrCodeWithBackground = imagecreatetruecolor(imagesx($qrCodeImage), imagesy($qrCodeImage));
        $white = imagecolorallocate($qrCodeWithBackground, 255, 255, 255);
        imagefill($qrCodeWithBackground, 0, 0, $white);

        // Copiar o QR Code para a nova imagem com fundo branco
        imagecopy($qrCodeWithBackground, $qrCodeImage, 0, 0, 0, 0, imagesx($qrCodeImage), imagesy($qrCodeImage));

        // Salvar a nova imagem com fundo branco
        imagepng($qrCodeWithBackground, $tempImagePath);

        // Calcular a coordenada X para centralizar o QR Code horizontalmente
        $qrCodeX = ($pdf->GetPageWidth() - $imageWidth) / 2;

        // Ajustar a coordenada Y para definir a posição vertical
        $qrCodeY = $pdf->GetPageHeight() - $imageWidth - 25; 

        // Adicionar o QR Code ao PDF
        $pdf->Image($tempImagePath, $qrCodeX, $qrCodeY, $imageWidth);

        // Remover a imagem temporária após adicioná-la ao PDF
        unlink($tempImagePath);

        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $imageWidth - 10); // Reduzir o valor para diminuir o espaço entre a logo e o texto

        // Substituir [numero_laudas] pelo número total de páginas do conteudo da primeira página
        $content = str_replace('[numero_laudas]', $total_laudas_str, $content);

        $pdf->setCellPadding(0, 0, 0, 10);

        // Criando a primeira página 
        $pdf->writeHTML($content, true, false, true, false, '');

        //Movendo a primeira página que é criada por ultimo, para a primeira posição do PDF
        $pdf->movePage($total_paginas, 1);

        $total_paginas = $pdf->getPage();

        // Percorrer as páginas do PDF para adicionar o rodapé
        for ($pagina = 1; $pagina <= $total_paginas; $pagina++) {
            // Definir a página atual para adicionar o conteúdo
            $pdf->setPage($pagina);

            // Posicionar o cursor no final da página
            $pdf->SetY(-25); 

            // Adicionar o texto no final da página
            $pdf->SetTextColor(200,200,200);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 5, 'Código da Certidão: ' . $cod_autenticidade_certidao . ' | ' . 'Consulta realizada em: ' . $data . ' às ' . $hora . 'min                                                                     ' . "Página " . $pdf->getAliasNumPage() . " de " . $pdf->getAliasNbPages() , 0, 1, 'L');

        }

        //ADICIONANDO LOGO PGE 
        $logo_pge = WWW_ROOT . 'img/logo_pge.png';
        $logoPgeWidth = 40; // Largura desejada da outra imagem
        // Centralizar a outra imagem horizontalmente
        $logoPgeX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        // Definir a posição vertical da outra imagem
        $logoPgeY = 10; 
        $novaPosicaoX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        $novaPosicaoY = 10;

        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $imageWidth -5); // Reduzir o valor para diminuir o espaço entre a logo e o texto

        // Percorrer as páginas do PDF para adicionar a logo no topo
        for ($pagina = 2; $pagina <= $total_paginas; $pagina++) {
            // Definir a página atual para adicionar o conteúdo
            $pdf->setPage($pagina);

            // Verificar se é a primeira página
            if ($pagina === 1) {
                // Adicionar a logo no topo da primeira página
                $pdf->Image($logo_pge, $logoPgeX, $logoPgeY, $logoPgeWidth);
            } else {
                // Definir a posição da logo nas páginas subsequentes (caso seja diferente)
                $pdf->Image($logo_pge, $novaPosicaoX, $novaPosicaoY, $logoPgeWidth);
            }

        }


        // Exibir o PDF na tela
        $pdf_string = $pdf->Output('', 'S');

        return $pdf_string;
    }


    public function gerarPdfConsultaAutenticidade($content,$cod_autenticidade_certidao,$data_consulta_autenticidade){

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Remover a linha horizontal no início do PDF
        $pdf->SetHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->SetMargins(20, 10, 20, 10);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 5); // evitar sobreposição de conteúdo

        //ADICIONANDO LOGO PGE NO TOPO
        $logo_pge = WWW_ROOT . 'img/logo_pge.png';
        $logoPgeWidth = 50; // Largura desejada da outra imagem
        // Centralizar a outra imagem horizontalmente
        $logoPgeX = ($pdf->GetPageWidth() - $logoPgeWidth) / 2;
        // Definir a posição vertical da outra imagem
        $logoPgeY = 5; 
        $pdf->Image($logo_pge, $logoPgeX, $logoPgeY, $logoPgeWidth);
        // Ajustar a posição vertical do texto abaixo da logo
        $pdf->SetY($pdf->GetY() + $logoPgeWidth -20); // Reduzir o valor para diminuir o espaço entre a logo e o texto
      
        $pdf->writeHTML($content, true, false, true, false, '');

        // Ajustar a posição vertical para adicionar o rodapé
        $pdf->SetY($pdf->GetY() + 120); // Espaço entre o conteúdo e o rodapé
        $pdf->SetTextColor(200, 200, 200);

        // Adicionar o rodapé personalizado no final da página
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Código da Certidão: ' . $cod_autenticidade_certidao . ' | ' . 'Consulta realizada em: ' . $data_consulta_autenticidade . '                                              ' . "Página " . $pdf->getAliasNumPage() . " de " . $pdf->getAliasNbPages() , 0, 1, 'L');
        // Exibir o PDF na tela
        $pdf_string = $pdf->Output('', 'S');
    
        return $pdf_string;
    }
    
}




