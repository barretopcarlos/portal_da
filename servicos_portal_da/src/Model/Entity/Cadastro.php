<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Cadastro extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}

?>