<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedAssertionException;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceComparisonHandler;
use webignition\BasilDataStructure\AssertionInterface;
use webignition\BasilParser\AssertionParser;

/**
 * @group poc208
 */
class ExistenceComparisonHandlerTest extends AbstractTestCase
{
    /**
     * @var ExistenceComparisonHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ExistenceComparisonHandler::createHandler();
    }

    /**
     * @dataProvider handleWrongValueTypeDataProvider
     */
    public function testHandleWrongValueType(AssertionInterface $assertion)
    {
        $this->expectExceptionObject(new UnsupportedAssertionException($assertion));

        $this->handler->handle($assertion);
    }

    public function handleWrongValueTypeDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'element reference' => [
                'model' => $assertionParser->parse('$elements.element_name exists'),
            ],
            'page element reference' => [
                'model' => $assertionParser->parse('$page_import_name.elements.element_name exists'),
            ],
        ];
    }
}
