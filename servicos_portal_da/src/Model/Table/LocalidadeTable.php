<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class LocalidadeTable extends Table
{
    public function initialize(array $config): void
    {

        $this->setTable('localidade');
        $this->setPrimaryKey('codigo_municipio_serventia');

        $this->hasMany('Cadastro', [
            'foreignKey' => 'codigo_localidade'
        ]);
    }
}


?>