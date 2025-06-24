<?php
// src/Form/MessageForm.php
namespace App\Form;

use App\Entity\Category;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        if ($options['pro']) {
            $builder->add('patient', EntityType::class, [
                'mapped' => false,
                'class' => User::class,
                'query_builder' => function (UserRepository $ur) {
                    return $ur->getUsersByRoleQueryBuilder('ROLE_PATIENT');
                },
                'choice_label' => 'name',
                'required' => true
            ]);
        } else {
            $builder->add('employer', EntityType::class, [
                'mapped' => false,
                'class' => User::class,
                'query_builder' => function (UserRepository $ur) {
                    return $ur->getUsersByAdminOrEmployeeRoleQueryBuilder();
                },
                'choice_label' => 'name',
                'required' => true
            ]);
        }

        $builder
            ->add('subject', TextType::class, [
                'required' => false,
                'label' => 'Sujet',
            ])
            ->add('content', TextareaType::class, [
                'mapped' => false,
                'label' => 'Message',
                'required' => true
            ])
            ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'pro' => false
        ]);
    }
}
