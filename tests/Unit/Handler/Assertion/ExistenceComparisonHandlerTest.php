<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilAssertionGenerator\AssertionGenerator;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceComparisonHandler;
use webignition\BasilModel\Assertion\ExaminationAssertion;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;

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
    public function testHandleWrongValueType(ExaminationAssertionInterface $assertion, string $expectedExceptionMessage)
    {
        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->handler->handle($assertion);
    }

    public function handleWrongValueTypeDataProvider(): array
    {
        $assertionGenerator = AssertionGenerator::createGenerator();

        return [
            'page element reference' => [
                'model' => $assertionGenerator->generate(
                    'page_import_name.elements.element_name exists'
                ),
                'expectedExceptionMessage' => 'Unsupported model "' . ExaminationAssertion::class . '"',
            ],
            'non-scalar object value' => [
                'model' => $assertionGenerator->generate(
                    '$data.key exists'
                ),
                'expectedExceptionMessage' => 'Unsupported model "' . ExaminationAssertion::class . '"',
            ],
        ];
    }
}
