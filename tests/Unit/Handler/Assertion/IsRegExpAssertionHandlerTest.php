<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IsRegExpAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTest;
use webignition\BasilModels\Assertion\AssertionInterface;

class IsRegExpAssertionHandlerTest extends AbstractResolvableTest
{
    use Assertion\CreateFromIsRegExpAssertionDataProviderTrait;

    /**
     * @dataProvider createFromIsRegExpAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = IsRegExpAssertionHandler::createHandler();

        $source = $handler->handle($assertion);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }
}
