<?php

namespace App\Action\User;

use App\Entity\User;
use Requestum\ApiBundle\Action\DeleteAction;

class UserDelete extends DeleteAction
{
    protected function getEntityName(): string
    {
        return User::class;
    }
}
