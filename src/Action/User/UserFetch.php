<?php

namespace App\Action\User;

use App\Entity\User;
use Requestum\ApiBundle\Action\FetchAction;

class UserFetch extends FetchAction
{
    protected function getEntityName(): string
    {
        return User::class;
    }
}
