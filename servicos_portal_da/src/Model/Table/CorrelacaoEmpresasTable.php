<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class CorrelacaoEmpresasTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('correlacao_empresas');

    }

    
}

?>