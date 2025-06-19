<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEmployee = $options['is_employee'] ?? false;
        $patients = $options['patients'] ?? [];
        $disablePatientSelect = $options['disable_patient_select'] ?? false;
        $availableSlots = $options['available_slots'] ?? [];

        // Ajout des champs communs
        $builder
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Catégories de soins',
            ])
            ->add('date', TextType::class, [
                'label' => 'Date (format JJ/MM/AAAA)',
                'mapped' => false,
                'required' => true,
            ])
            ->add('time', HiddenType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => ['id' => 'appointment_form_time'],
            ]);

        // Partie spécifique selon rôle
        if ($isEmployee && !$disablePatientSelect) {
            $builder->add('patient', EntityType::class, [
                'class' => User::class,
                'choices' => $patients,
                'choice_label' => fn(User $user) => $user->getName(),
                'label' => 'Sélectionner un patient',
            ]);
        };
        if (!$isEmployee) {
            $builder->add('socialSecurityNumber', TextType::class, [
                'label' => 'Numéro de sécurité sociale',
                'mapped' => false,
                'required' => true,
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'available_slots' => [],
            'is_employee' => false,
            'disable_patient_select' => false,
            'patients' => [],
        ]);
    }
}
