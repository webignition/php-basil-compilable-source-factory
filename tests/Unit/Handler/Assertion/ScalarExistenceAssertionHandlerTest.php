<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Handler\Assertion\ScalarExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ScalarExistenceAssertionHandlerTest extends AbstractResolvableTestCase
{
    use Assertion\CreateFromScalarExistsAssertionDataProviderTrait;

    /**
     * @dataProvider createFromScalarExistsAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        Metadata $metadata,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = ScalarExistenceAssertionHandler::createHandler();

        $source = $handler->handle($assertion, $metadata);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }
}
