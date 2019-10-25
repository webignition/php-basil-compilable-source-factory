<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\Factory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::createFactory();
    }

    public function testCreateSourceThrowsUnsupportedModelException()
    {
        $this->expectException(UnsupportedModelException::class);

        $test = new Test(
            'test name',
            new Configuration('', ''),
            []
        );

        $this->factory->createSource($test);
    }
}
