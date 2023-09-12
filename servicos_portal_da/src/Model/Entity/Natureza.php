<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Natureza extends Entity
{
    protected $_accessible = [
        '*' => true,
        'codigo' => false
    ];
}

?>