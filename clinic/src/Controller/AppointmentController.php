<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Message\AppointmentCreated;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AppointmentController extends AbstractController
{
    #[Route('/appointment/new', name: 'appointment_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ): Response {
        $appointment = new Appointment();

        // Если у пользователя роль доктора, подставляем его ID
        if ($this->isGranted('ROLE_DOCTOR')) {
            $user = $this->getUser();
            $appointment->setDoctorId($user->getId());
        }

        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            // Отправляем сообщение о создании записи
            $bus->dispatch(new AppointmentCreated($appointment->getId()));

            $this->addFlash('success', 'Запись создана, уведомление отправлено');

            return $this->redirectToRoute('patient_show', ['id' => $appointment->getPatient()->getId()]);
        }

        return $this->render('appointment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}