<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\Action\ActionFactory;

trait CreateFromReloadActionDataProviderTrait
{
    public function createFromReloadActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'no-arguments action (reload)' => [
                'action' => $actionFactory->createFromActionString('reload'),
                'expectedContent' => Block::fromContent([
                    '{{ CRAWLER }} = {{ CLIENT }}->reload()',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CRAWLER,
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
        ];
    }
}
