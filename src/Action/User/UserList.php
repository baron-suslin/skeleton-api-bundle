<?php

namespace App\Action\User;

use App\Entity\User;
use Requestum\ApiBundle\Action\ListAction;

class UserList extends ListAction
{
    protected function getEntityName(): string
    {
        return User::class;
    }
}
