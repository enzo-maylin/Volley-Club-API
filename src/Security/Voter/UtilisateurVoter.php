<?php

namespace App\Security\Voter;

use App\Entity\Utilisateur;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UtilisateurVoter extends Voter
{
    public const UTILISATEUR_DELETE = 'PERM_DELETE';

    public function __construct( private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::UTILISATEUR_DELETE])
            && $subject instanceof Utilisateur;
    }
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        switch ($attribute) {
            case self::UTILISATEUR_DELETE:
                if($user != null) {
                    if ($this->security->isGranted('ROLE_ADMIN') || $subject == $user) {
                        return true;
                    }
                }
                return false;
        }
        return false;
    }
}