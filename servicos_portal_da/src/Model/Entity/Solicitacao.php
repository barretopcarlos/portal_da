<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Solicitacao extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}

?>