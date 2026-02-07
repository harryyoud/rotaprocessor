<?php

namespace App\Security;

use App\Entity\SyncJob;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SyncJobVoter extends Voter {
    const VIEW_LOGS = "view_logs";

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool {
        if ($attribute != self::VIEW_LOGS) {
            return false;
        }

        if (!$subject instanceof SyncJob) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        /** @var SyncJob $subject */

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $subject->getOwner()->getId() === $user->getId();
    }
}
