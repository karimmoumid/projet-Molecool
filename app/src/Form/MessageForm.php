<?php
// src/Form/MessageForm.php
namespace App\Form;

use App\Entity\Category;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class MessageForm extends AbstractType
{
    // Méthode pour construire le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout d'un champ pour sélectionner un patient si l'option 'pro' est vraie
        if ($options['pro']) {
            $builder->add('patient', EntityType::class, [
                'mapped' => false, // Ce champ n'est pas mappé à une propriété de l'entité
                'class' => User::class, // Classe de l'entité utilisée pour ce champ
                'query_builder' => function (UserRepository $ur) {
                    return $ur->getUsersByRoleQueryBuilder('ROLE_PATIENT'); // Requête pour obtenir les utilisateurs avec le rôle 'ROLE_PATIENT'
                },
                'choice_label' => 'name', // Propriété de l'entité à afficher comme libellé
                'required' => true // Ce champ est obligatoire
            ]);
        } else {
            // Ajout d'un champ pour sélectionner un employé si l'option 'pro' est fausse
            $builder->add('employer', EntityType::class, [
                'mapped' => false, // Ce champ n'est pas mappé à une propriété de l'entité
                'class' => User::class, // Classe de l'entité utilisée pour ce champ
                'query_builder' => function (UserRepository $ur) {
                    return $ur->getUsersByAdminOrEmployeeRoleQueryBuilder(); // Requête pour obtenir les utilisateurs avec les rôles d'admin ou d'employé
                },
                'choice_label' => 'name', // Propriété de l'entité à afficher comme libellé
                'required' => true // Ce champ est obligatoire
            ]);
        }

        // Ajout des autres champs du formulaire
        $builder
            ->add('subject', TextType::class, [
                'required' => false, // Ce champ n'est pas obligatoire
                'label' => 'Sujet', // Libellé du champ
            ])
            ->add('content', TextareaType::class, [
                'mapped' => false, // Ce champ n'est pas mappé à une propriété de l'entité
                'label' => 'Message', // Libellé du champ
                'required' => true // Ce champ est obligatoire
            ])
            ->add('files', FileType::class, [
                'mapped' => false, // Ce champ n'est pas mappé à une propriété de l'entité
                'multiple' => true, // Permet de télécharger plusieurs fichiers
                'required' => false, // Ce champ n'est pas obligatoire
                'constraints' => [
                    new All([ // Applique les contraintes à chaque fichier
                        'constraints' => [
                            new File([ // Contrainte de type de fichier
                                'maxSize' => '5M', // Taille maximale des fichiers
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'application/pdf',
                                ],
                                'mimeTypesMessage' => 'Merci de télécharger un fichier valide (jpeg, png ou pdf).', // Message d'erreur si le type de fichier est invalide
                            ]),
                        ],
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,application/pdf', // Types de fichiers acceptés
                ],
            ]);
    }

    // Méthode pour configurer les options du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class, // Classe de l'entité associée au formulaire
            'pro' => false // Valeur par défaut de l'option 'pro'
        ]);
    }
}
