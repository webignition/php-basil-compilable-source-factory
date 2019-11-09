<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class AssertionCallFactoryTest extends AbstractTestCase
{
    /**
     * @var AssertionCallFactory
     */
    private $factory;

    /**
     * @var TestCodeGenerator
     */
    private $testCodeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = AssertionCallFactory::createFactory();
        $this->testCodeGenerator = TestCodeGenerator::create();
    }

    /**
     * @dataProvider createValueComparisonAssertionCallDataProvider
     */
    public function testCreateValueComparisonAssertionCall(
        SourceInterface $expectedValueAssignment,
        SourceInterface $actualValueAssignment,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->factory->createValueComparisonAssertionCall(
            $expectedValueAssignment,
            $actualValueAssignment,
            $expectedValuePlaceholder,
            $actualValuePlaceholder,
            $assertionTemplate
        );

        $this->assertSourceContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());

        $code = $this->testCodeGenerator->createPhpUnitTestForLineList($source, [
            VariableNames::EXPECTED_VALUE => '$expectedValue',
            VariableNames::EXAMINED_VALUE => '$examinedValue',
            VariableNames::PHPUNIT_TEST_CASE => '$this',
        ]);

        eval($code);
    }

    public function createValueComparisonAssertionCallDataProvider(): array
    {
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        return [
            'assert equals' => [
                'expectedValueAssignment' => new Statement(
                    $expectedValuePlaceholder . ' = "equals"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
                ),
                'actualValueAssignment' => new Statement(
                    $examinedValuePlaceholder . ' = "equals"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXPECTED }} = "equals"',
                    '{{ EXAMINED }} = "equals"',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert not equals' => [
                'expectedValueAssignment' => new Statement(
                    $expectedValuePlaceholder . ' = "equals"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
                ),
                'actualValueAssignment' => new Statement(
                    $examinedValuePlaceholder . ' = "not equals"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXPECTED }} = "equals"',
                    '{{ EXAMINED }} = "not equals"',
                    '{{ PHPUNIT }}->assertNotEquals({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert string contains string' => [
                'expectedValueAssignment' => new Statement(
                    $expectedValuePlaceholder . ' = "needle"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
                ),
                'actualValueAssignment' => new Statement(
                    $examinedValuePlaceholder . ' = "haystack containing needle"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXPECTED }} = "needle"',
                    '{{ EXAMINED }} = "haystack containing needle"',
                    '{{ PHPUNIT }}->assertStringContainsString((string) {{ EXPECTED }}, (string) {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert string not contains string' => [
                'expectedValueAssignment' => new Statement(
                    $expectedValuePlaceholder . ' = "needle"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
                ),
                'actualValueAssignment' => new Statement(
                    $examinedValuePlaceholder . ' = "haystack"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXPECTED }} = "needle"',
                    '{{ EXAMINED }} = "haystack"',
                    '{{ PHPUNIT }}->assertStringNotContainsString((string) {{ EXPECTED }}, (string) {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert matches' => [
                'expectedValueAssignment' => new Statement(
                    $expectedValuePlaceholder . ' = "/pattern/"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
                ),
                'actualValueAssignment' => new Statement(
                    $examinedValuePlaceholder . ' = "matches pattern"',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXPECTED }} = "/pattern/"',
                    '{{ EXAMINED }} = "matches pattern"',
                    '{{ PHPUNIT }}->assertRegExp({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
        ];
    }

    /**
     * @dataProvider createValueExistenceAssertionCallDataProvider
     */
    public function testCreateValueExistenceAssertionCall(
        SourceInterface $assignment,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->factory->createValueExistenceAssertionCall(
            $assignment,
            $variablePlaceholder,
            $assertionTemplate
        );

        $this->assertSourceContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());

        $code = $this->testCodeGenerator->createPhpUnitTestForLineList($source, [
            VariableNames::EXAMINED_VALUE => '$examinedValue',
            VariableNames::PHPUNIT_TEST_CASE => '$this',
        ]);

        eval($code);
    }

    public function createValueExistenceAssertionCallDataProvider(): array
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        return [
            'assert true' => [
                'assignment' => new Statement(
                    $examinedValuePlaceholder . ' = "value" !== null',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_TRUE_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXAMINED }} = "value" !== null',
                    '{{ PHPUNIT }}->assertTrue({{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert false' => [
                'assignment' => new Statement(
                    $examinedValuePlaceholder . ' = null !== null',
                    (new Metadata())
                        ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
                ),
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_FALSE_TEMPLATE,
                'expectedContent' => LineList::fromContent([
                    '{{ EXAMINED }} = null !== null',
                    '{{ PHPUNIT }}->assertFalse({{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $examinedValuePlaceholder,
                    ])),
            ],
        ];
    }
}
