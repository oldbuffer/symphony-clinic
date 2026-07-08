<?php

namespace App\DataFixtures;

use App\Entity\Patient;
use App\Entity\Appointment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Создаём 10 пациентов
        $patients = [];
        for ($i = 0; $i < 10; $i++) {
            $patient = new Patient();
            $patient->setName($faker->name());
            $patient->setPhone($faker->optional()->phoneNumber());
            $patient->setEmail($faker->unique()->safeEmail());
            $patient->setBirthDate($faker->optional()->dateTimeBetween('-80 years', '-18 years'));
            $manager->persist($patient);
            $patients[] = $patient;
        }

        // Создаём 20 записей, случайно привязывая к пациентам
        for ($i = 0; $i < 20; $i++) {
            $appointment = new Appointment();
            $appointment->setPatient($faker->randomElement($patients));
            $appointment->setDoctorId($faker->numberBetween(1, 5));
            $appointment->setAppointmentDate($faker->dateTimeBetween('now', '+1 month'));
            // status и createdAt уже установлены в конструкторе
            $manager->persist($appointment);
        }

        $manager->flush();
    }
}