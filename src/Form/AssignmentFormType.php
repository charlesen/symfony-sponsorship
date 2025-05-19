<?php

namespace App\Form;

use App\Entity\Assignment;
use App\Entity\AssignmentType as AssignmentTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700',
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => AssignmentTypeEntity::class,
                'choice_label' => 'title',
                'label' => 'Type d\'assignment',
                'placeholder' => 'Sélectionnez un type',
                'required' => true,
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700',
                ],
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700',
                ],
            ])
            ->add('targetUrl', UrlType::class, [
                'label' => 'URL cible',
                'required' => false,
                'default_protocol' => 'https',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    'placeholder' => 'https://exemple.com',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => [
                    'class' => 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'ml-2 block text-sm text-gray-700',
                ],
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => 'Date d\'expiration',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assignment::class,
        ]);
    }
}
