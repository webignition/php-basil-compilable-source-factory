<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Handler\Assertion\IsRegExpAssertionHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class IsRegExpAssertionHandlerTest extends AbstractResolvableTestCase
{
    use Assertion\CreateFromIsRegExpAssertionDataProviderTrait;

    /**
     * @dataProvider createFromIsRegExpAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = IsRegExpAssertionHandler::createHandler();

        $source = $handler->handle($assertion);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }
}
