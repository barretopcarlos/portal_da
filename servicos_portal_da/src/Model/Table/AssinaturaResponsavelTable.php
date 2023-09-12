<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class AssinaturaResponsavelTable extends Table{
    
    public function initialize(array $config): void{
        parent::initialize($config);

        $this->setTable('assinatura_responsavel');
        $this->setPrimaryKey('id');

        $this->belongsTo('especializada', [
            'foreignKey' => 'id_especializada',
        ]);
    }
}

?>