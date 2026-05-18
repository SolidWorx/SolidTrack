<?php

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Client;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'name',
                'autocomplete' => true,
            ])
            ->add('hourlyRate', null, ['attr' => ['placeholder' => 'Workspace Default']])
            ->add('color', ColorType::class, [
                'attr' => [
                    'data-controller' => 'color-picker',
                    'data-color-picker-default-value' => Project::DEFAULT_COLOR,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
