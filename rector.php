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

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withImportNames(importShortClasses: true)
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withSets([
        // General
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::NAMED_ARGS,
        SetList::RECTOR_PRESET,
        SetList::CARBON,
        SetList::PHP_VERSION_BASED_SET,
        SetList::TYPE_DECLARATION_DOCBLOCKS,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,

        // PHPUnit
        PHPUnitSetList::PHPUNIT_MOCK_TO_STUB,
        PHPUnitSetList::PHPUNIT_NARROW_ASSERTS,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::COMPOSER_BASED,

        // Doctrine
        DoctrineSetList::COMPOSER_BASED,
        DoctrineSetList::TYPED_COLLECTIONS,
        DoctrineSetList::TYPED_COLLECTIONS_DOCBLOCKS,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES,

        // Symfony
        SymfonySetList::COMPOSER_BASED,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::CONFIGS,
    ]);
