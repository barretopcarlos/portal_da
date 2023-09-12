<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class CertidaoTipoTable extends Table{

    public function initialize(array $config): void{
        
        parent::initialize($config);

        $this->setTable('certidao_tipo');
        $this->setPrimaryKey('id');

        $this->hasMany('Solicitacao', [
            'foreignKey' => 'id_certidao_tipo',
        ]);
    }
}

?>