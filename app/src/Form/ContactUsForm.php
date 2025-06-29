<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactUsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [])
            ->add('subject', ChoiceType::class, [
                'choices' => [
                    'Besoin d\'information sur une analyse' => 'Besoin d\'information sur une analyse',
                    'Soucis de connexion' => 'Soucis de connexion',
                    'Soucis sur la prise de rendez-vous' => 'Soucis sur la prise de rendez-vous',
                ],
                'placeholder' => '--Veillez-saisir-votre-raison--',
                'required' => true,
            ])
            ->add('message', TextareaType::class, [])
            ->add('Envoyer', SubmitType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
