<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class LinkParametroTable extends Table{

    public function initialize(array $config): void{

        $this->setTable('link_parametro');
        $this->setPrimaryKey('id');

        $this->belongsTo('link_tipo', [
            'foreignKey' => 'id_link_tipo'
        ]);

        $this->belongsTo('ambiente', [
            'foreignKey' => 'id_ambiente',
        ]);
    }
}

?>