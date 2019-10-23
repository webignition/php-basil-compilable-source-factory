<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\Factory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromBackActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromClickActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromForwardActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromReloadActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSetActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSubmitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitForActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromMatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromNotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\CreateFromValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\StatementListInterface;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    use CreateFromValueDataProviderTrait;
    use CreateFromExcludesAssertionDataProviderTrait;
    use CreateFromExistsAssertionDataProviderTrait;
    use CreateFromIncludesAssertionDataProviderTrait;
    use CreateFromIsAssertionDataProviderTrait;
    use CreateFromIsNotAssertionDataProviderTrait;
    use CreateFromMatchesAssertionDataProviderTrait;
    use CreateFromNotExistsAssertionDataProviderTrait;
    use CreateFromBackActionDataProviderTrait;
    use CreateFromForwardActionDataProviderTrait;
    use CreateFromReloadActionDataProviderTrait;
    use CreateFromClickActionDataProviderTrait;
    use CreateFromSetActionDataProviderTrait;
    use CreateFromSubmitActionDataProviderTrait;
    use CreateFromWaitActionDataProviderTrait;
    use CreateFromWaitForActionDataProviderTrait;
    use UnhandledValueDataProviderTrait;

    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::createFactory();
    }

    /**
     * @dataProvider createFromExcludesAssertionDataProvider
     * @dataProvider createFromExistsAssertionDataProvider
     * @dataProvider createFromIncludesAssertionDataProvider
     * @dataProvider createFromIsAssertionDataProvider
     * @dataProvider createFromIsNotAssertionDataProvider
     * @dataProvider createFromMatchesAssertionDataProvider
     * @dataProvider createFromNotExistsAssertionDataProvider
     * @dataProvider createFromBackActionDataProvider
     * @dataProvider createFromForwardActionDataProvider
     * @dataProvider createFromReloadActionDataProvider
     * @dataProvider createFromClickActionDataProvider
     * @dataProvider createFromSetActionDataProvider
     * @dataProvider createFromSubmitActionDataProvider
     * @dataProvider createFromWaitActionDataProvider
     * @dataProvider createFromWaitForActionDataProvider
     */
    public function testCreateSourceSuccess(
        object $model,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->factory->createSource($model);

        $this->assertInstanceOf(StatementListInterface::class, $statementList);
        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertEquals($expectedMetadata, $statementList->getMetadata());
    }

    /**
     * @dataProvider unhandledValueDataProvider
     * @dataProvider unhandledModelDataProvider
     */
    public function testCreateSourceThrowsNonTranspilableModelException(object $model)
    {
        $this->expectException(NonTranspilableModelException::class);

        $this->assertFalse($this->factory->createSource($model));
    }

    public function unhandledModelDataProvider(): array
    {
        return [
            'stdClass' => [
                'model' => new \stdClass(),
            ],
        ];
    }
}
