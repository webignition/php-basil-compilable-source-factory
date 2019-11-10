<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\CreateFromValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandlerTest extends AbstractTestCase
{
    use CreateFromValueDataProviderTrait;

    /**
     * @var ScalarValueHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ScalarValueHandler();
    }

    /**
     * @dataProvider createFromValueDataProvider
     */
    public function testHandle(
        ValueInterface $model,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($model);

        $this->assertInstanceOf(SourceInterface::class, $source);

        if ($source instanceof SourceInterface) {
            $this->assertSourceContentEquals($expectedContent, $source);
            $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
        }
    }
}
