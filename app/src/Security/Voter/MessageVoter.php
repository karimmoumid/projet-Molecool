<?php
namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class MessageVoter extends Voter
{
    // Constantes pour les différentes actions (permissions) que le voter peut évaluer
    public const EDIT = 'MESSAGE_EDIT';
    public const VIEW = 'MESSAGE_VIEW';
    public const DELETE = 'MESSAGE_DELETE';

    // Méthode pour déterminer si le voter supporte l'attribut et le sujet donnés
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie si l'attribut est l'un de ceux définis et si le sujet est une instance de Message
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Message;
    }

    // Méthode pour voter sur l'attribut donné
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur est anonyme, ne pas accorder l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Vérifie si l'utilisateur est soit l'expéditeur soit le destinataire du message
        $isOwner = $user === $subject->getSender() || $user === $subject->getRecipient();

        // Évalue l'attribut pour déterminer si l'accès doit être accordé
        switch ($attribute) {
            case self::VIEW:
                // Accorder l'accès si l'utilisateur est le propriétaire (expéditeur ou destinataire)
                return $isOwner;
            case self::EDIT:
            case self::DELETE:
                // Par exemple : seul le propriétaire peut modifier ou supprimer
                return $isOwner;
        }

        return false;
    }
}
