<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class Especializada extends Table{
    
    public function initialize(array $config): void{
        parent::initialize($config);

        $this->setTable('especializada');
        $this->setPrimaryKey('id');

        $this->hasMany('assinatura_responsavel', [
            'foreignKey' => 'id_especializada',
        ]);
    }
}

?>