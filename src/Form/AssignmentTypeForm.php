<?php

namespace App\Form;

use App\Entity\AssignmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssignmentTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'assignment_type.form.title.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'assignment_type.title.not_blank',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'assignment_type.title.max_length',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'assignment_type.form.title.placeholder',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'assignment_type.form.description.label',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'assignment_type.form.description.placeholder',
                ],
            ])
            ->add('icon', TextType::class, [
                'label' => 'assignment_type.form.icon.label',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'assignment_type.form.icon.placeholder',
                ],
                'help' => 'assignment_type.form.icon.help',
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssignmentType::class,
        ]);
    }
}
