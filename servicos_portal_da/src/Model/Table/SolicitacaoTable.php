<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class SolicitacaoTable extends Table{

    public function initialize(array $config): void{

        $this->setTable('solicitacao');
        $this->setPrimaryKey('id');

        $this->hasMany('certidao', [
            'foreignKey' => 'id_solicitacao'
        ]);

        $this->belongsTo('contribuinte_tipo', [
            'className' => 'ContribuinteTipo',
            'foreignKey' => 'id_contribuinte_tipo'
        ]);

        $this->belongsTo('certidao_tipo', [
            'className' => 'CertidaoTipo',
            'foreignKey' => 'id_certidao_tipo',
        ]);
    }
}

?>