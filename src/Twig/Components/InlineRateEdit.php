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

namespace App\Twig\Components;

use App\Entity\Project;
use App\Form\InlineRateType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class InlineRateEdit extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public Project $project;

    #[LiveProp(writable: true)]
    public bool $editing = false;

    #[LiveProp(writable: true)]
    public string $rate = '';

    /**
     * @var list<string>
     */
    #[LiveProp]
    public array $formErrors = [];

    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function mount(Project $project): void
    {
        $this->project = $project;
        $hourlyRate = $this->project->getHourlyRate();
        $this->rate = $hourlyRate !== null ? (string) $hourlyRate : '';
    }

    #[LiveAction]
    public function startEdit(): void
    {
        $this->editing = true;
        $this->formErrors = [];
    }

    #[LiveAction]
    public function save(): void
    {
        $form = $this->createForm(InlineRateType::class);
        $form->submit(['hourlyRate' => $this->rate !== '' ? $this->rate : null]);

        if (! $form->isValid()) {
            $this->formErrors = array_values(array_map(
                static fn (FormError $e): string => $e->getMessage(),
                iterator_to_array($form->get('hourlyRate')->getErrors()),
            ));

            return;
        }

        /** @var array{hourlyRate: float|null} $data */
        $data = $form->getData();
        $this->project->setHourlyRate($data['hourlyRate']);
        $this->projectRepository->save($this->project);

        $hourlyRate = $this->project->getHourlyRate();
        $this->rate = $hourlyRate !== null ? (string) $hourlyRate : '';
        $this->editing = false;
        $this->formErrors = [];
    }

    #[LiveAction]
    public function cancel(): void
    {
        $hourlyRate = $this->project->getHourlyRate();
        $this->rate = $hourlyRate !== null ? (string) $hourlyRate : '';
        $this->editing = false;
        $this->formErrors = [];
    }
}
