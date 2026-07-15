<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => function (Patient $patient) {
                    return $patient->getName() . ' (' . $patient->getEmail() . ')';
                },
                'placeholder' => 'Выберите пациента',
            ])
            ->add('doctorId', IntegerType::class, [
                'label' => 'ID доктора',
                'attr' => ['min' => 1, 'max' => 5], // пока фиксированный диапазон
            ])
            ->add('appointmentDate', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Дата и время приёма',
            ])
            // status и createdAt не добавляем
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
