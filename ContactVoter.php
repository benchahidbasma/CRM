<?php

namespace App\Security\Voter;

use App\Entity\Contact;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ContactVoter extends Voter
{
    public const VIEW = 'CONTACT_VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Contact;
    }

    protected function voteOnAttribute(string $attribute, $contact, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) return true;
        return $contact->getOwner()->getId() === $user->getId();
    }
}
