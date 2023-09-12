<?php 

namespace App\Model\Table;

use Cake\ORM\Table;

class EnvolvidoTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('rdg_envolvidos');
        $this->setPrimaryKey('ISN_RDG_ENVOLVIDOS');

    }
}

?>