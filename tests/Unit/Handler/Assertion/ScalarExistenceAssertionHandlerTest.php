<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ScalarExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTest;
use webignition\BasilModels\Assertion\AssertionInterface;

class ScalarExistenceAssertionHandlerTest extends AbstractResolvableTest
{
    use Assertion\CreateFromScalarExistsAssertionDataProviderTrait;

    /**
     * @dataProvider createFromScalarExistsAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = ScalarExistenceAssertionHandler::createHandler();

        $source = $handler->handle($assertion);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }
}
