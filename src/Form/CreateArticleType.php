<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CreateArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'label_attr' => [
                    'id' => 'titleLabel'
                ],
                'attr' => [
                    'placeholder' => 'Enter Title'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Title is required'
                    ]),
                    new Length([
                        'min' => 4,
                        'max' => 200,
                        'minMessage' => 'Title must be {{ limit }} or more characters long',
                        'maxMessage' => 'Title cannot be more than {{ limit }} character long'
                    ])
                ]
            ])
            ->add('featureImage', FileType::class, [
                "label" => "Feature Image",
                'attr' => array(
                    'placeholder' => 'Select an image',
                ),
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        "message" => "Please select an image"
                    ]),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ]),
                ],
            ])
            ->add('content', HiddenType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Content is required'
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Content must be {{ limit }} or more characters long'
                    ])
                ]
            ])
            ->add('isCommentsAllowed', CheckboxType::class, [
                'label' => 'Allow Comments?',
                'required' => false
            ])
            ->add('shouldPublish', CheckboxType::class, [
                'label' => 'Should Publish?',
                'required' => false
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
