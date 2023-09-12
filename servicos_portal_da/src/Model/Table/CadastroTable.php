<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class CadastroTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('cadastro');
        $this->setPrimaryKey('id');

        $this->belongsTo('Natureza', [
            'foreignKey' => 'codigo_natureza',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Localidade', [
            'foreignKey' => 'codigo_localidade',
            'joinType' => 'INNER'
        ]);

    }
}

?>