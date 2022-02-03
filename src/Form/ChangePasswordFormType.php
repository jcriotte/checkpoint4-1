<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'block bg-gray-200 px-2 py-0.5 mb-3'
                    ],
                    'label_attr' => [
                        'class' => 'block text-sm text-left',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Regex([
                            'pattern' => '/(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{6,}/',
                            'message' => 'Votre mot de passe doit contenir 
                            au minimum 6 caractères, dont un chiffre et une lettre'
                        ])
                    ],
                    'label' => 'New password',
                    'error_bubbling' => true
                ],
                'second_options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'block bg-gray-200 px-2 py-0.5 mb-3'
                    ],
                    'label_attr' => [
                        'class' => 'block text-sm text-left',
                    ],
                    'label' => 'Repeat Password',
                    'error_bubbling' => true
                ],
                'invalid_message' => 'Les mot de passes ne sont pas identiques',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
