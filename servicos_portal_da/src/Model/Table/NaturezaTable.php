<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class NaturezaTable extends Table
{
    public function initialize(array $config): void
    {

        $this->setTable('natureza');
        $this->setPrimaryKey('codigo');

        $this->hasMany('Cadastro', [
            'foreignKey' => 'codigo_natureza'
        ]);
    }
}


?>