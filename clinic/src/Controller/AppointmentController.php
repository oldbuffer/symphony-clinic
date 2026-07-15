<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Appointment;
use App\Form\AppointmentType;

final class AppointmentController extends AbstractController
{
    #[Route('/appointment/new', name: 'appointment_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
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

            $this->addFlash('success', 'Запись успешно создана');

            return $this->redirectToRoute('patient_show', ['id' => $appointment->getPatient()->getId()]);
        }

        return $this->render('appointment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
