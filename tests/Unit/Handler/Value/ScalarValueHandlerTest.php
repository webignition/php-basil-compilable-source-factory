<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\BrowserPropertyDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\CreateFromValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\PagePropertyProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandlerTest extends AbstractHandlerTest
{
    use BrowserPropertyDataProviderTrait;
    use CreateFromValueDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use LiteralValueDataProviderTrait;
    use PagePropertyProviderTrait;
    use UnhandledValueDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return ScalarValueHandler::createHandler();
    }

    /**
     * @dataProvider browserPropertyDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider pagePropertyDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->handler->handles($model));
    }

    /**
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->handler->handles($model));
    }

    /**
     * @dataProvider createFromValueDataProvider
     */
    public function testTranspile(
        ValueInterface $model,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->handler->createSource($model);

        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertEquals($expectedMetadata, $statementList->getMetadata());
    }
}
