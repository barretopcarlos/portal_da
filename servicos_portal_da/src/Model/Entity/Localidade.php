<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Localidade extends Entity
{
    protected $_accessible = [
        '*' => true,
        'codigo_serventia' => false
    ];
}

?>