<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\Action\ActionFactory;

trait CreateFromWaitForActionDataProviderTrait
{
    public function createFromWaitForActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (wait-for), element identifier' => [
                'action' => $actionFactory->createFromActionString(
                    'wait-for ".selector"'
                ),
                'expectedContent' => Block::fromContent([
                    '{{ CRAWLER }} = {{ CLIENT }}->waitFor(\'.selector\')',
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
