<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\ModelFactory;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\EnvironmentValueFactory;

class EnvironmentValueFactoryTest extends TestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(string $value, EnvironmentValue $expectedEnvironmentValue): void
    {
        $factory = EnvironmentValueFactory::createFactory();

        $environmentValue = $factory->create($value);

        $this->assertSame($expectedEnvironmentValue->getProperty(), $environmentValue->getProperty());
        $this->assertSame($expectedEnvironmentValue->getDefault(), $environmentValue->getDefault());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
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
