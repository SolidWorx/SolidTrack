<?php

declare(strict_types=1);

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Capturing a period that has already been worked, rather than timing it live.
 *
 * The two datetime fields hand over plain DateTimeImmutable instances; TimeEntry's
 * setters normalise those to Carbon. "End after start" is enforced on the entity
 * so the running tracker (which has no end yet) is covered by the same rule.
 */
final class ManualTimeEntryType extends AbstractTimeEntryType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
        parent::__construct($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            // Same fields as the tracker bar, reworded for work already done and
            // given the labels a stacked form needs (the tracker bar has none).
            ->add(
                'description',
                null,
                [
                    'label' => $this->translator->trans('What did you work on?'),
                    'attr' => [
                        'placeholder' => $this->translator->trans('Description'),
                    ],
                    'required' => false,
                ]
            )
            // Symfony drops `placeholder` for a multiple choice field, so TomSelect
            // has nothing to show in an empty tags box. The attribute survives, and
            // TomSelect reads it off the select.
            ->add(
                'tags',
                EntityType::class,
                [
                    'class' => Tag::class,
                    'choice_label' => static fn (Tag $tag) => sprintf(
                        '<span class="status-dot align-middle me-2" style="--swp-status-color: %s"></span>%s',
                        htmlspecialchars($tag->getColor(), \ENT_QUOTES),
                        htmlspecialchars($tag->getName(), \ENT_QUOTES),
                    ),
                    'options_as_html' => true,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'autocomplete' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => $this->translator->trans('Add tags...'),
                    ],
                ]
            )
            ->add(
                'billable',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => $this->translator->trans('Billable'),
                ]
            )
            ->add(
                'dateStart',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'label' => $this->translator->trans('Starts at'),
                    'constraints' => [
                        new NotNull(message: 'Enter when the work started.'),
                    ],
                ]
            )
            ->add(
                'dateEnd',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'label' => $this->translator->trans('Ends at'),
                    'constraints' => [
                        new NotNull(message: 'Enter when the work ended.'),
                    ],
                ]
            );
    }
}
