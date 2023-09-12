<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class PessoaFisica extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}

?>