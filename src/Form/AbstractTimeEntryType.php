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

use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\TimeEntry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The fields every time entry shares, however it is captured: the running
 * tracker bar and the manual-entry modal both build on this.
 *
 * @extends AbstractType<TimeEntry>
 */
abstract class AbstractTimeEntryType extends AbstractType
{
    /**
     * Deliberately outside `framework.csrf_protection.stateless_token_ids`
     * (see config/packages/csrf.yaml).
     *
     * Stateless CSRF renders a sentinel value that the platform's
     * `csrf_protection` controller only swaps for a real token on a `submit`
     * event — so a LiveComponent action fired from a non-submit control (the
     * tracker's Stop button) would post the sentinel, and SameOriginCsrfTokenManager
     * rejects that once the session has used double-submit. Falling through to the
     * session-backed manager gives these forms a real token that round-trips
     * through the component's `formValues` on every kind of interaction.
     */
    public const CSRF_TOKEN_ID = 'time_entry';

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'description',
                options: [
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
                    'choice_label' => static fn (Project $project): string => sprintf(
                        '<span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: %s;"></span>%s',
                        htmlspecialchars($project->getColor(), \ENT_QUOTES),
                        htmlspecialchars($project->getName(), \ENT_QUOTES),
                    ),
                    'group_by' => static fn (Project $project): ?string => $project->getClient()?->getName(),
                    'options_as_html' => true,
                    'autocomplete' => true,
                    'placeholder' => $this->translator->trans('Select a project'),
                    'required' => false,
                ]
            )
            ->add(
                'tags',
                EntityType::class,
                [
                    'class' => Tag::class,
                    'choice_label' => static fn (Tag $tag): string => sprintf(
                        '<span class="status-dot align-middle me-2" style="--swp-status-color: %s"></span>%s',
                        htmlspecialchars($tag->getColor(), \ENT_QUOTES),
                        htmlspecialchars($tag->getName(), \ENT_QUOTES),
                    ),
                    'options_as_html' => true,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'autocomplete' => true,
                    'placeholder' => $this->translator->trans('Add tags...'),
                    'label' => false,
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
            'csrf_token_id' => self::CSRF_TOKEN_ID,
        ]);
    }
}
