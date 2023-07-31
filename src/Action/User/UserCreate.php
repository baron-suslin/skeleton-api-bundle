<?php

namespace App\Action\User;

use App\Entity\User;
use App\Form\User\UserCreateForm;
use Requestum\ApiBundle\Action\CreateAction;
use Symfony\Component\HttpFoundation\Request;

class UserCreate extends CreateAction
{
    protected function getEntityName(): string
    {
        return User::class;
    }

    protected function getFormTypeClass(): string
    {
        return UserCreateForm::class;
    }

    public function executeAction(Request $request)
    {
        return parent::executeAction($request);
    }
}
