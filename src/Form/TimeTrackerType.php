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

use App\Entity\Project;
use App\Entity\TimeEntry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimeTrackerType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'description',
                null,
                [
                    'attr' => [
                        'placeholder' => $this->translator->trans('What are you working on?'),
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'project',
                EntityType::class,
                [
                    'class' => Project::class,
                    //'choice_label' => static fn (Project $project) => sprintf('%s<br><small>(%s)</small>', $project->getName(), $project->getClient()?->getName()),
                    'choice_label' => 'name',
                    'group_by' => static fn (Project $project) => $project->getClient()?->getName(),
                    'options_as_html' => true,
                    'autocomplete' => true,
                    'placeholder' => $this->translator->trans('Select a project'),
                    'required' => false,
                ]
            )
            ->add(
                'billable',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TimeEntry::class,
            // This form is driven by a LiveComponent, which has its own CSRF
            // protection at the action level. Form-level CSRF would also reject
            // submitForm() calls triggered by non-submit interactions (e.g. the
            // stop button is an <a>, not a form submit).
            'csrf_protection' => false,
        ]);
    }
}
