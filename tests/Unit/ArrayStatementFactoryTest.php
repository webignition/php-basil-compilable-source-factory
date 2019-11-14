<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\ArrayStatementFactory;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Line\StatementInterface;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\DataSet\DataSetCollectionInterface;

class ArrayStatementFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ArrayStatementFactory
     */
    private $arrayStatementFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->arrayStatementFactory = ArrayStatementFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(DataSetCollectionInterface $dataSetCollection, StatementInterface $expectedStatement)
    {
        $arrayStatement = $this->arrayStatementFactory->create($dataSetCollection);

        $this->assertEquals($expectedStatement, $arrayStatement);
    }

    public function createDataProvider(): array
    {
        return [
//            'empty' => [
//                'dataSetCollection' => new DataSetCollection(),
//                'expectedStatement' => new Statement('return []'),
//            ],
//            'single data set with single key:value numerical name' => [
//                'dataSetCollection' => new DataSetCollection([
//                    new DataSet('0', [
//                        'key1' => 'value1',
//                    ]),
//                ]),
//                'expectedStatement' => new Statement(
//                    "return [
//    '0' => [
//        'key1' => 'value1',
//    ],
//]"
//                ),
//            ],
//            'single data set with single key:value string name' => [
//                'dataSetCollection' => new DataSetCollection([
//                    new DataSet('data-set-one', [
//                        'key1' => 'value1',
//                    ]),
//                ]),
//                'expectedStatement' => new Statement(
//                    "return [
//    'data-set-one' => [
//        'key1' => 'value1',
//    ],
//]"
//                ),
//            ],
//            'single data set with single key:value string name containing single quotes' => [
//                'dataSetCollection' => new DataSetCollection([
//                    new DataSet("'data-set-one'", [
//                        "'key1'" => "'value1'",
//                    ]),
//                ]),
//                'expectedStatement' => new Statement(
//                    "return [
//    '\'data-set-one\'' => [
//        '\'key1\'' => '\'value1\'',
//    ],
//]"
//                ),
//            ],
//            'single data set with multiple key:value numerical name' => [
//                'dataSetCollection' => new DataSetCollection([
//                    new DataSet('0', [
//                        'key1' => 'value1',
//                        'key2' => 'value2',
//                    ]),
//                ]),
//                'expectedStatement' => new Statement(
//                    "return [
//    '0' => [
//        'key1' => 'value1',
//        'key2' => 'value2',
//    ],
//]"
//                ),
//            ],
//            'multiple data sets with multiple key:value numerical name' => [
//                'dataSetCollection' => new DataSetCollection([
//                    new DataSet('0', [
//                        'key1' => 'value1',
//                        'key2' => 'value2',
//                    ]),
//                    new DataSet('1', [
//                        'key1' => 'value3',
//                        'key2' => 'value4',
//                    ]),
//                ]),
//                'expectedStatement' => new Statement(
//                    "return [
//    '0' => [
//        'key1' => 'value1',
//        'key2' => 'value2',
//    ],
//    '1' => [
//        'key1' => 'value3',
//        'key2' => 'value4',
//    ],
//]"
//                ),
//            ],
            'array keys are ordered alphabetically' => [
                'dataSetCollection' => new DataSetCollection([
                    new DataSet('0', [
                        'zebra' => 'zebra value',
                        'apple' => 'apple value',
                        'bee' => 'bee value',
                    ]),
                ]),
                'expectedStatement' => new Statement(
                    "return [
    '0' => [
        'apple' => 'apple value',
        'bee' => 'bee value',
        'zebra' => 'zebra value',
    ],
]"
                ),
            ],
        ];
    }
}
