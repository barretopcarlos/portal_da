<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class CorrelacaoEmpresas extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' =>false
    ];
}

?>