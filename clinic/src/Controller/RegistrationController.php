<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Получаем plainPassword из формы (оно не связано с сущностью)
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Хешируем пароль и устанавливаем его пользователю
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // Назначаем роль по умолчанию
            $user->setRoles(['ROLE_PATIENT']);

            $entityManager->persist($user);
            $entityManager->flush();

            // Добавляем flash-сообщение и перенаправляем на страницу входа
            $this->addFlash('success', 'Регистрация прошла успешно. Теперь вы можете войти.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}