<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Line\ArrayExpression;
use webignition\BasilCompilableSourceFactory\ArrayExpressionFactory;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;

class ArrayExpressionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ArrayExpressionFactory
     */
    private $arrayStatementFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->arrayStatementFactory = ArrayExpressionFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        DataSetCollectionInterface $dataSetCollection,
        ArrayExpression $expectedExpression
    ) {
        $arrayStatement = $this->arrayStatementFactory->create($dataSetCollection);

        $this->assertEquals($expectedExpression, $arrayStatement);
    }

    public function createDataProvider(): array
    {
        return [
            'empty' => [
                'dataSetCollection' => new DataSetCollection([]),
                'expectedExpression' => new ArrayExpression([]),
            ],
            'single data set with single key:value numerical name' => [
                'dataSetCollection' => new DataSetCollection([
                    0 => [
                        'key1' => 'value1',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    0 => [
                        'key1' => 'value1',
                    ],
                ]),
            ],
            'single data set with single key:value string name' => [
                'dataSetCollection' => new DataSetCollection([
                    'data-set-one' => [
                        'key1' => 'value1',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    'data-set-one' => [
                        'key1' => 'value1',
                    ],
                ]),
            ],
            'single data set with single key:value string name containing single quotes' => [
                'dataSetCollection' => new DataSetCollection([
                    "'data-set-one'" => [
                        "'key1'" => "'value1'",
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    "'data-set-one'" => [
                        "'key1'" => "'value1'",
                    ],
                ]),
            ],
            'single data set with multiple key:value numerical name' => [
                'dataSetCollection' => new DataSetCollection([
                    '0' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    '0' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
            ],
            'multiple data sets with multiple key:value numerical name' => [
                'dataSetCollection' => new DataSetCollection([
                    '0' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                    '1' => [
                        'key1' => 'value3',
                        'key2' => 'value4',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    '0' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                    '1' => [
                        'key1' => 'value3',
                        'key2' => 'value4',
                    ],
                ]),
            ],
        ];
    }
}
