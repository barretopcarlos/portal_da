<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Certidao extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}

?>