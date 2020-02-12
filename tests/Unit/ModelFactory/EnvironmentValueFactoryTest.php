<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\ModelFactory;

use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\ModelFactory\EnvironmentValueFactory;

class EnvironmentValueFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $value, EnvironmentValue $expectedEnvironmentValue)
    {
        $this->markTestSkipped();

        $factory = EnvironmentValueFactory::createFactory();

        $environmentValue = $factory->create($value);

        $this->assertSame($expectedEnvironmentValue->getProperty(), $environmentValue->getProperty());
        $this->assertSame($expectedEnvironmentValue->getDefault(), $environmentValue->getDefault());
    }

    public function createDataProvider(): array
    {
        return [
            'no default' => [
                'value' => '$env.KEY',
                'expectedEnvironmentValue' => new EnvironmentValue('KEY', null),
            ],
            'has default' => [
                'value' => '$env.KEY|"default_value"',
                'expectedEnvironmentValue' => new EnvironmentValue('KEY', 'default_value'),
            ],
            'has default containing whitespace' => [
                'value' => '$env.KEY|"default value"',
                'expectedEnvironmentValue' => new EnvironmentValue('KEY', 'default value'),
            ],
            'environment parameter, empty default' => [
                'value' => '$env.KEY|""',
                'expectedEnvironmentValue' => new EnvironmentValue('KEY', ''),
            ],
            'environment parameter, missing default' => [
                'value' => '$env.KEY|',
                'expectedEnvironmentValue' => new EnvironmentValue('KEY', null),
            ],
            'environment parameter, has escaped-quote default' => [
                'value' => '$env.KEY|"\"default_value\""',
                'expectedEnvironmentValue' => new EnvironmentValue('KEY', '"default_value"'),
            ],
        ];
    }
}
