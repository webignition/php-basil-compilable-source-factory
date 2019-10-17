<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\Factory;
use webignition\BasilCompilableSourceFactory\NonTranspilableModelException;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::createFactory();
    }

    /**
     * @dataProvider unhandledModelDataProvider
     */
    public function testCreateSourceForUnhandledModel(object $model)
    {
        $this->expectException(NonTranspilableModelException::class);

        $this->factory->createSource($model);
    }

    public function unhandledModelDataProvider(): array
    {
        return [
            'stdClass' => [
                'model' => new \stdClass(),
            ],
        ];
    }
}
