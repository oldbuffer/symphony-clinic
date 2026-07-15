<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

final class AppointmentVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Appointment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false; // неавторизованный
        }

        /** @var Appointment $appointment */
        $appointment = $subject;

        switch ($attribute) {
            case 'EDIT':
                // Админ может всё
                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    return true;
                }
                // Доктор может редактировать, если это его запись (предположим, что doctorId соответствует ID пользователя)
                // Но у нас пока нет связи доктора с User. Допустим, что у пользователя есть роль ROLE_DOCTOR и его ID == doctorId
                if (in_array('ROLE_DOCTOR', $user->getRoles()) && $appointment->getDoctorId() === $user->getId()) {
                    return true;
                }
                break;
        }
        return false;
    }
}
