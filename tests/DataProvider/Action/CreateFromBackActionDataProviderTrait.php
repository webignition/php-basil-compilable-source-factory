<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\Action\ActionFactory;

trait CreateFromBackActionDataProviderTrait
{
    public function createFromBackActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'no-arguments action (back)' => [
                'action' => $actionFactory->createFromActionString('back'),
                'expectedContent' => Block::fromContent([
                    '{{ CRAWLER }} = {{ CLIENT }}->back()',
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
