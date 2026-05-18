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

namespace App\Report;

use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Ulid;

final readonly class ReportFilter
{
    /**
     * @param list<Ulid> $tagIds
     */
    public function __construct(
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public ?Ulid $projectId = null,
        public ?Ulid $clientId = null,
        public array $tagIds = [],
        public ?bool $billable = null,
    ) {
    }

    /**
     * @param list<string> $tagIds
     */
    public static function fromScalars(
        string $from,
        string $to,
        ?string $projectId = null,
        ?string $clientId = null,
        array $tagIds = [],
        ?string $billable = null,
    ): self {
        $start = CarbonImmutable::parse($from)->startOfDay();
        $end = CarbonImmutable::parse($to)->endOfDay();

        $resolvedBillable = match ($billable) {
            '1', 'true' => true,
            '0', 'false' => false,
            default => null,
        };

        return new self(
            from: $start,
            to: $end,
            projectId: $projectId !== null && $projectId !== '' && Ulid::isValid($projectId) ? Ulid::fromString($projectId) : null,
            clientId: $clientId !== null && $clientId !== '' && Ulid::isValid($clientId) ? Ulid::fromString($clientId) : null,
            tagIds: array_values(array_filter(array_map(
                static fn (string $id): ?Ulid => Ulid::isValid($id) ? Ulid::fromString($id) : null,
                $tagIds,
            ))),
            billable: $resolvedBillable,
        );
    }
}
