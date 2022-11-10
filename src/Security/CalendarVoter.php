<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\WebDavCalendar;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CalendarVoter extends Voter {
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool {
        if (!in_array($attribute, [self::DELETE, self::EDIT], true)) {
            return false;
        }

        if (!$subject instanceof WebDavCalendar) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
        /** @var WebDavCalendar $subject */

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($subject->getId() === null) {
            return true;
        }

        return $subject->getOwner()->getId() === $user->getId();
    }
}