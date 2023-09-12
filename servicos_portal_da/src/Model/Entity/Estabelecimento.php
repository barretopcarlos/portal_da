<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Estabelecimento extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}

?>