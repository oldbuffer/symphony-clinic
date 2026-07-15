<?php

namespace App\Command;

use App\Repository\AppointmentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Отправляет напоминания о завтрашних приёмах',
)]
class SendRemindersCommand extends Command
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tomorrow = new \DateTime('tomorrow');
        $start = (clone $tomorrow)->setTime(0, 0, 0);
        $end = (clone $tomorrow)->setTime(23, 59, 59);

        $appointments = $this->appointmentRepository->createQueryBuilder('a')
            ->join('a.patient', 'p')
            ->addSelect('p')
            ->where('a.appointmentDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        if (empty($appointments)) {
            $output->writeln('Нет приёмов на завтра.');
            return Command::SUCCESS;
        }

        foreach ($appointments as $appointment) {
            $patient = $appointment->getPatient();
            if (!$patient || !$patient->getEmail()) {
                continue;
            }

            $email = (new Email())
                ->from('noreply@clinic.local')
                ->to($patient->getEmail())
                ->subject('Напоминание о завтрашнем приёме')
                ->text(sprintf(
                    "Здравствуйте, %s!\n\nНапоминаем, что завтра %s у вас приём у доктора (ID: %d).\n\nС уважением, Клиника.",
                    $patient->getName(),
                    $appointment->getAppointmentDate()->format('d.m.Y H:i'),
                    $appointment->getDoctorId()
                ));

            $this->mailer->send($email);
            $output->writeln(sprintf('Напоминание отправлено для записи #%d (пациент %s)', $appointment->getId(), $patient->getEmail()));
        }

        $output->writeln('Готово.');
        return Command::SUCCESS;
    }
}