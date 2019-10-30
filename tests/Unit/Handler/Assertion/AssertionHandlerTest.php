<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromMatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromNotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\MatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Assertion\AssertionInterface;

class AssertionHandlerTest extends AbstractHandlerTest
{
    use ExcludesAssertionDataProviderTrait;
    use ExistsAssertionDataProviderTrait;
    use IncludesAssertionDataProviderTrait;
    use IsAssertionDataProviderTrait;
    use IsNotAssertionDataProviderTrait;
    use MatchesAssertionDataProviderTrait;
    use NotExistsAssertionDataProviderTrait;
    use UnhandledAssertionDataProviderTrait;

    use CreateFromExcludesAssertionDataProviderTrait;
    use CreateFromExistsAssertionDataProviderTrait;
    use CreateFromIncludesAssertionDataProviderTrait;
    use CreateFromIsAssertionDataProviderTrait;
    use CreateFromIsNotAssertionDataProviderTrait;
    use CreateFromMatchesAssertionDataProviderTrait;
    use CreateFromNotExistsAssertionDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return AssertionHandler::createHandler();
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider existsAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     * @dataProvider matchesAssertionDataProvider
     * @dataProvider notExistsAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->handler->handles($model));
    }

    /**
     * @dataProvider unhandledAssertionDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->handler->handles($model));
    }

    /**
     * @dataProvider createFromExcludesAssertionDataProvider
     * @dataProvider createFromExistsAssertionDataProvider
     * @dataProvider createFromIncludesAssertionDataProvider
     * @dataProvider createFromIsAssertionDataProvider
     * @dataProvider createFromIsNotAssertionDataProvider
     * @dataProvider createFromMatchesAssertionDataProvider
     * @dataProvider createFromNotExistsAssertionDataProvider
     */
    public function testCreateSource(
        AssertionInterface $assertion,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createSource($assertion);

        $this->assertSourceContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }
}
