<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Envolvido extends Entity
{
    protected $_accessible = [
        '*' => true,
        'ISN_RDG_ENVOLVIDOS' => false
    ];
}

?>