<?php

namespace App\Form;

use App\Entity\Assignment;
use App\Entity\AssignmentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('isActive')
            ->add('expiresAt', null, [
                'widget' => 'single_text',
            ])
            ->add('targetUrl')
            ->add('points')
            ->add('type', EntityType::class, [
                'class' => AssignmentType::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assignment::class,
        ]);
    }
}
