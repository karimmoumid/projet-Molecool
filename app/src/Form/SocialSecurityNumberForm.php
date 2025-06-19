<?php

namespace App\Form;

use App\Entity\SocialSecurityNumber;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SocialSecurityNumberForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number',TextType::class,[
                'constraints' => [
                    new NotBlank(message: 'Numéro de sécurité sociale obligatoire'),
                    new Length(min: 13, max: 13,exactMessage: 'Vous avez saisi {{ value_length }} ca doit etre {{ limit }} charactere')
                ],
                'label' => 'Numéro de la sécurité sociale',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SocialSecurityNumber::class,
        ]);
    }
}
