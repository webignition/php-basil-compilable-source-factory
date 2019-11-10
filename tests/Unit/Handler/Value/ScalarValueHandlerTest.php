<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\CreateFromValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
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
        BlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($model);

        if ($source instanceof BlockInterface) {
            $this->assertBlockContentEquals($expectedContent, $source);
            $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
        }
    }
}
