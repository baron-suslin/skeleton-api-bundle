<?php

namespace App\Listener\Entity;

use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserListener
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function prePersist(User $user): void
    {
        $this->encodePassword($user);
    }

    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('plainPassword')) {
            $this->encodePassword($user);
        }
    }

    private function encodePassword(User $user): void
    {
        if (null !== $user->getPlainPassword()) {
            $password = $this->hasher->hashPassword($user, $user->getPlainPassword());

            $user
                ->setPassword($password)
                ->eraseCredentials()
            ;
        }
    }
}
