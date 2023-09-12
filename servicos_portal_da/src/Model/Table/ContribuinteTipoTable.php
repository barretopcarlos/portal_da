<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class ContribuinteTipoTable extends Table{
    
    public function initialize(array $config): void{
        parent::initialize($config);

        $this->setTable('contribuinte_tipo');
        $this->setPrimaryKey('id');

        $this->hasMany('Solicitacao', [
            'foreignKey' => 'id_contribuinte_tipo',
        ]);
    }
}

?>