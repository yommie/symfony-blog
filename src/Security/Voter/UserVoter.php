<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    private const BAN_USER = "CAN_BAN_USER";

    private const SUPPORTED = [self::BAN_USER];

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, self::SUPPORTED) && $subject instanceof User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch($attribute) {
            case self::BAN_USER:
                return $this->canBanUser($subject);
        }

        return false;
    }

    private function canBanUser(User $user) {
        if(!$this->security->isGranted("ROLE_ADMIN")) {
            return false;
        }

        if(!$this->security->isGranted("ROLE_SUPER_ADMIN") && $user->isGranted("ROLE_ADMIN")) {
            return false;
        }

        return $this->security->isGranted("ROLE_SUPER_ADMIN") || in_array(self::BAN_USER, $user->getPermissions());
    }
}
