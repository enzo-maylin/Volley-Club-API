<?php

namespace App\Security\Voter;

use App\Entity\Evenement;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EvenementVoter extends Voter
{
    public const EVENEMENT_EDIT = 'PERM_EDIT';

    public function __construct( private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EVENEMENT_EDIT])
            && $subject instanceof Evenement;
    }
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if(is_null($user)){
            return false;
        }
        switch ($attribute) {
            case self::EVENEMENT_EDIT:
                return $this->security->isGranted('ROLE_ADMIN') || $subject->getOrganisateur() == $user;
            default:
                return false;
        }
    }
}