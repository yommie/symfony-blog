<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Username',
                'label_attr' => [
                    'id' => 'usernameLabel'
                ],
                'attr' => [
                    'placeholder' => 'Enter Username'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Username is required'
                    ]),
                    new Length([
                        'min' => 4,
                        'max' => 10,
                        'minMessage' => 'Username must be {{ limit }} or more characters long',
                        'maxMessage' => 'Username cannot be more than {{ limit }} character long'
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords do not match',
                'required' => false,
                'first_options' => [
                    'attr' => [
                        'placeholder' => 'Enter Password'
                    ],
                    'label' => 'Password'
                ],
                'second_options' => [
                    'attr' => [
                        'placeholder' => 'Enter Password Again'
                    ],
                    'label' => 'Confirm Password'
                ],
                'options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password'
                        ]),
                        new Length([
                            'min' => 6,
                            'max' => 100,
                            'minMessage' => 'Password must be {{ limit }} characters or more',
                            'maxMessage' => 'Password cannot be more than {{ limit }} characters'
                        ])
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
