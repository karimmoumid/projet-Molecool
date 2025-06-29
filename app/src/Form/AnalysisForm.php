<?php

namespace App\Form;

use App\Entity\Analysis;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class AnalysisForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',options: [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Description',
                'mapped' => false,
            ])
            ->add('prix',IntegerType::class,[
                'mapped' => false,
                'constraints' => [
                    new Positive(message: 'Veillez enter un prix positive'),
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Categorie',
                'placeholder' => 'Choisissez une catégorie',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une catégorie.']),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Analysis::class,
        ]);
    }
}
