<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class FuncionarioTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('funcionario');

    }

    
}

?>