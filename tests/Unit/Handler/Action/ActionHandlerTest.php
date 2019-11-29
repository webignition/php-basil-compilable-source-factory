<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromBackActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromClickActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromForwardActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromReloadActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSetActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSubmitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitForActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilParser\ActionParser;

class ActionHandlerTest extends AbstractTestCase
{
    use CreateFromBackActionDataProviderTrait;
    use CreateFromClickActionDataProviderTrait;
    use CreateFromForwardActionDataProviderTrait;
    use CreateFromReloadActionDataProviderTrait;
    use CreateFromSetActionDataProviderTrait;
    use CreateFromSubmitActionDataProviderTrait;
    use CreateFromWaitActionDataProviderTrait;
    use CreateFromWaitForActionDataProviderTrait;

    /**
     * @dataProvider createFromBackActionDataProvider
     * @dataProvider createFromClickActionDataProvider
     * @dataProvider createFromForwardActionDataProvider
     * @dataProvider createFromReloadActionDataProvider
     * @dataProvider createFromSetActionDataProvider
     * @dataProvider createFromSubmitActionDataProvider
     * @dataProvider createFromWaitActionDataProvider
     * @dataProvider createFromWaitForActionDataProvider
     */
    public function testHandleSuccess(
        ActionInterface $action,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = ActionHandler::createHandler();
        $source = $handler->handle($action);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        ActionInterface $action,
        UnsupportedActionException $expectedException
    ) {
        $handler = ActionHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action, identifier not dom identifier' => [
                'action' => $actionParser->parse('click $elements.element_name'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('click $elements.element_name'),
                    new UnsupportedIdentifierException('$elements.element_name')
                ),
            ],
            'interaction action, attribute identifier' => [
                'action' => $actionParser->parse('submit $".selector".attribute_name'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('submit $".selector".attribute_name'),
                    new UnsupportedIdentifierException('$".selector".attribute_name')
                ),
            ],
            'set action, identifier is not dom identifier' => [
                'action' => $actionParser->parse('set $elements.element_name to "value"'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('set $elements.element_name to "value"'),
                    new UnsupportedIdentifierException('$elements.element_name')
                ),
            ],
            'set action, identifier is attribute reference' => [
                'action' => $actionParser->parse('set $".selector".attribute_name to "value"'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('set $".selector".attribute_name to "value"'),
                    new UnsupportedIdentifierException('$".selector".attribute_name')
                ),
            ],
            'set action, value is null' => [
                'action' => $actionParser->parse('set $".selector"'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('set $".selector"'),
                    new UnsupportedValueException(null)
                ),
            ],
            'set action, value is unsupported' => [
                'action' => $actionParser->parse('set $".selector" to $elements.element_name'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('set $".selector" to $elements.element_name'),
                    new UnsupportedValueException('$elements.element_name')
                ),
            ],
            'wait action, value is empty' => [
                'action' => $actionParser->parse('wait'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('wait'),
                    new UnsupportedValueException('')
                ),
            ],
            'wait action, value is unsupported' => [
                'action' => $actionParser->parse('wait $elements.element_name'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('wait $elements.element_name'),
                    new UnsupportedValueException('$elements.element_name')
                ),
            ],
            'wait-for action, identifier is not dom identifier' => [
                'action' => $actionParser->parse('wait-for $elements.element_name'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('wait-for $elements.element_name'),
                    new UnsupportedIdentifierException('$elements.element_name')
                ),
            ],
            'wait-for action, identifier is attribute reference' => [
                'action' => $actionParser->parse('wait-for $".selector".attribute_name'),
                'expectedException' => new UnsupportedActionException(
                    $actionParser->parse('wait-for $".selector".attribute_name'),
                    new UnsupportedIdentifierException('$".selector".attribute_name')
                ),
            ],
        ];
    }
}
