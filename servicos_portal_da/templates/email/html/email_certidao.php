<!-- src/Template/Email/html/certidao_email.ctp -->

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?= $titulo ?></title>
</head>
<body>
    <h1><?= $titulo ?></h1>
    <p>
        <strong>Contribuinte:</strong> <?= $nome ?><br>
        <strong>Tipo de Certidão:</strong> <?= $tipo_certidao ?><br>
        <strong>Nº da Solicitação:</strong> <?= $id_solicitacao ?><br>
        <strong>Data da Solicitação:</strong> <?= $data ?><br>
    </p>
</body>
</html>
