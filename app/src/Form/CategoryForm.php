<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class CategoryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class,[
                'label' => 'Description',
            ])
            ->add('preview', TextareaType::class,[
                'label' => 'Résumé',
            ])
            ->add('deadline', TextareaType::class,[
                'label' => 'Délai d\'analyse',
            ])
            ->add('equipments', IntegerType::class,[
                'mapped' => false,
                'label' => 'Nombre des équipments',
                'constraints' => [
                    new Positive(message: 'Veillez selectionner au moins un équipement'),
                ]
            ])
            ->add('equipements', CollectionType::class, [
                'mapped' => false,
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
