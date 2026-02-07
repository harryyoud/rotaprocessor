<?php

namespace App\Security;

use App\Entity\Placement;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlacementVoter extends Voter {
    const EDIT = "edit";
    const VIEW_JOBS = "viewjobs";
    const UPLOAD = "upload";
    const DELETE = "delete";

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool {
        if (
            !in_array($attribute, [
                self::DELETE,
                self::UPLOAD,
                self::VIEW_JOBS,
                self::EDIT,
            ])
        ) {
            return false;
        }

        if (!$subject instanceof Placement) {
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
        /** @var Placement $subject */

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
