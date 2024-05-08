<?php

namespace App\Security\Voter;

use App\Entity\CollectionContainer;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CollectionItemVoter extends Voter
{
    private const NEW = 'new';
    private const EDIT = 'edit';
    private const DELETE = 'delete';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::NEW, self::EDIT, self::DELETE])
            && $subject instanceof CollectionContainer;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::NEW, self::EDIT, self::DELETE => $this->isOwner($subject, $user),
            default => false,
        };
    }

    private function isOwner(CollectionContainer $container, User $user): bool
    {
        return $container->getUser() === $user;
    }
}
