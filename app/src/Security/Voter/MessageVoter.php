<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class MessageVoter extends Voter
{
    public const EDIT = 'MESSAGE_EDIT';
    public const VIEW = 'MESSAGE_VIEW';

    public const DELETE = 'MESSAGE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Message;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // L’utilisateur doit être soit l'expéditeur soit le destinataire
        $isOwner = $user === $subject->getSender() || $user === $subject->getRecipient();

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
                return $isOwner; // Accès si propriétaire (sender ou recipient)
            case self::EDIT:
            case self::DELETE:
                // Par exemple : seul le propriétaire peut modifier ou supprimer
                return $isOwner;
        }

        return false;
    }
}
