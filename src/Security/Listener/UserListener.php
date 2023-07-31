<?php

namespace App\Security\Listener;

use App\Entity\User;
use League\Bundle\OAuth2ServerBundle\Event\UserResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserProviderInterface $provider,
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::USER_RESOLVE => 'onResolve',
        ];
    }

    public function onResolve(UserResolveEvent $event): void
    {
        try {
            /** @var User $user */
            $user = $this->provider->loadUserByIdentifier($event->getUsername());

            if (!$this->hasher->isPasswordValid($user, $event->getPassword())) {
                return;
            }

            $event->setUser($user);
        } catch (UserNotFoundException $e) {
            return;
        }
    }
}
