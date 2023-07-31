<?php

namespace App\Action\User;

use App\Entity\User;
use App\Form\User\UserUpdateForm;
use Requestum\ApiBundle\Action\UpdateAction;

class UserUpdate extends UpdateAction
{
    protected function getFormTypeClass(): string
    {
        return UserUpdateForm::class;
    }

    protected function getEntityName(): string
    {
        return User::class;
    }
}
