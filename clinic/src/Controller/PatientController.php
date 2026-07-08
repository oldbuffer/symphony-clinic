<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PatientRepository;

final class PatientController extends AbstractController
{
    #[Route('/patients', name: 'patients_list')]
    public function index(PatientRepository $patientRepository): Response
    {
        $patients = $patientRepository->findAll();

        return $this->render('patient/index.html.twig', [
            'patients' => $patients,
        ]);
    }
}
