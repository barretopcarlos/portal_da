<?php
$DescricaoPagina = 'Portal do contribuinte';
?>

<?php echo $this->Html->css('https://fonts.googleapis.com/icon?family=Material+Icons'); ?>


<!DOCTYPE html>
<htm lang="pt-br">
<head>
    <?= $this->Html->charset() ?>
    <?= $this->Html->meta('viewport', 'width=device-width, initial-scale=1') ?>

    <title>
        <?= $DescricaoPagina ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'cake', 'bootstrap/bootstrap.min','header','footer','fonts.css']) ?>
    <?= $this->Html->script(['jquery-3.6.4.min','bootstrap/bootstrap.min']) ?>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-light-blue no-print">
        
        <div class="container no-print header-todo">   
            <div class="social">
                <span class="social_icon"><?= $this->Html->image('header/facebook.png', ['alt' => 'Facebook PGE RJ']); ?></span>
                <span class="social_icon"><?= $this->Html->image('header/whatsapp.png', ['alt' => 'Fale Conosco - WhatsApp PGE RJ']); ?></span>
                <span class="social_icon"><?= $this->Html->image('header/instagram.png', ['alt' => 'Nos siga no Instagram']); ?></span>
                <span class="social_icon"><?= $this->Html->image('header/twitter.png', ['alt' => 'Siga a PGE RJ no Twitter']); ?></span>
                <span class="social_icon"><?= $this->Html->image('header/linkedin.png', ['alt' => 'Estamos no Linkedin']); ?></span>
                <span class="social_icon ytb"><?= $this->Html->image('header/youtube.png', ['alt' => 'PGE RJ no Youtube']); ?></span>
            </div>
        </div>
        
    </nav>

    <nav class="navbar navbar-expand-lg navbar-light bg-light nav-principal no-print">
        <div class="container header-todo">
            <?= $this->Html->image('header/pgeRjVetor.svg', ['alt' => 'Pge RJ', 'class' =>'lgo_pge',"title"=>"Procuradoria Geral do Estado do Rio de janeiro"]); ?>
        </div>
    </nav>
    
    <!-- <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Cake</span>PHP</a>
        </div>
    </nav> -->

    <main class="main">
        <div class="container header-todo">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    
    <!-- Grafismo Footer -->
    <div class="graf no-print">
        <?= $this->Html->image('footer/footerVetor.png', ['alt' => 'Pge RJ', 'class' =>'grafismo']); ?>
    </div>

    <footer class="footer_pge no-print">
        <div class="text_footer">
            <?= $this->Html->image('footer/logoPge_white.png', ['alt' => 'Pge RJ', 'class' =>'logo_white']); ?>
            <p>Rua do Carmo, 27 - Centro - Rio de Janeiro</p>
        </div>
    </footer>

    <div class="bottom_bar_footer no-print" role="contentinfo" aria-label="Informações adicionais sobre os direitos autorais"><p class="text_bottom_bar_footer" >2023 © Copyright - Todos os direitos reservados.</p></div>

</body>
</html>
