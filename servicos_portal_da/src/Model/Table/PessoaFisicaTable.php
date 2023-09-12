<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class PessoaFisicaTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('pessoa_fisica');
        $this->setPrimaryKey('id');
    }
}

?>