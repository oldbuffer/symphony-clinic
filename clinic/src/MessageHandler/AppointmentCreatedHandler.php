<?php

namespace App\MessageHandler;

use App\Message\AppointmentCreated;
use App\Repository\AppointmentRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AppointmentCreatedHandler
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private MailerInterface $mailer
    ) {}

    public function __invoke(AppointmentCreated $message): void
    {
        $appointment = $this->appointmentRepository->find($message->getAppointmentId());
        if (!$appointment) {
            return;
        }

        $email = (new Email())
            ->from('noreply@clinic.local')
            ->to($appointment->getPatient()->getEmail())
            ->subject('Запись на приём подтверждена')
            ->html(sprintf(
                '<p>Здравствуйте, %s!</p><p>Вы записаны на приём к доктору (ID: %d) на %s.</p>',
                $appointment->getPatient()->getName(),
                $appointment->getDoctorId(),
                $appointment->getAppointmentDate()->format('d.m.Y H:i')
            ));

        $this->mailer->send($email);
    }
}