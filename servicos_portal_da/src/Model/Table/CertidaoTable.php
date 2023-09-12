<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class CertidaoTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('certidao');
        $this->setPrimaryKey('id');

        $this->belongsTo('Solicitacao', [
            'foreignKey' => 'id_solicitacao',
            'joinType' => 'INNER'
        ]);
    }

    
}

?>