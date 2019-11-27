<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;

class EnvironmentValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate(string $value, string $expectedProperty, ?string $expectedDefault)
    {
        $environmentValue = new EnvironmentValue($value);

        $this->assertSame($expectedProperty, $environmentValue->getProperty());
        $this->assertSame($expectedDefault, $environmentValue->getDefault());
    }

    public function createDataProvider(): array
    {
        return [
            'no default' => [
                'value' => '$env.KEY',
                'expectedProperty' => 'KEY',
                'expectedDefault' => null,
            ],
//            'environment parameter, has default' => [
//                'value' => '$env.KEY|"default_value"',
//                'expectedValue' => new ObjectValue(
//                    ObjectValueType::ENVIRONMENT_PARAMETER,
//                    '$env.KEY|"default_value"',
//                    'KEY',
//                    'default_value'
//                ),
//            ],
//            'environment parameter, has default containing whitespace' => [
//                'value' => '$env.KEY|"default value"',
//                'expectedValue' => new ObjectValue(
//                    ObjectValueType::ENVIRONMENT_PARAMETER,
//                    '$env.KEY|"default value"',
//                    'KEY',
//                    'default value'
//                ),
//            ],
//            'environment parameter, empty default' => [
//                'value' => '$env.KEY|""',
//                'expectedValue' => new ObjectValue(
//                    ObjectValueType::ENVIRONMENT_PARAMETER,
//                    '$env.KEY|""',
//                    'KEY',
//                    ''
//                ),
//            ],
//            'environment parameter, missing default' => [
//                'value' => '$env.KEY|',
//                'expectedValue' => new ObjectValue(
//                    ObjectValueType::ENVIRONMENT_PARAMETER,
//                    '$env.KEY|',
//                    'KEY',
//                    ''
//                ),
//            ],
//            'environment parameter, has escaped-quote default' => [
//                'value' => '$env.KEY|"\"default_value\""',
//                'expectedValue' => new ObjectValue(
//                    ObjectValueType::ENVIRONMENT_PARAMETER,
//                    '$env.KEY|"\"default_value\""',
//                    'KEY',
//                    '"default_value"'
//                ),
//            ],
        ];
    }
}
