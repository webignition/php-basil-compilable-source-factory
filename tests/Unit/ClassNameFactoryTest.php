<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilModels\Model\Test\NamedTestInterface;

class ClassNameFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(NamedTestInterface $test, string $expectedClassName): void
    {
        self::assertSame($expectedClassName, (new ClassNameFactory())->create($test));
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'path test.yml, browser chrome' => [
                'test' => $this->createTest('test.yml', 'chrome'),
                'expectedClassName' => 'Generated8b691caed69e58a60c79e9d1592a9f6aTest',
            ],
            'path test.yml, browser firefox' => [
                'test' => $this->createTest('test.yml', 'firefox'),
                'expectedClassName' => 'Generated4b8f9518f66bf58061cb0d263ff58430Test',
            ],
            'path /sub/test.yml' => [
                'test' => $this->createTest('/sub/test.yml', 'chrome'),
                'expectedClassName' => 'Generated4ba6193a8ed1a54deca52dd8a2b1fc12Test',
            ],
        ];
    }

    private function createTest(string $path, string $browser): NamedTestInterface
    {
        $test = \Mockery::mock(NamedTestInterface::class);

        $test
            ->shouldReceive('getName')
            ->andReturn($path)
        ;

        $test
            ->shouldReceive('getBrowser')
            ->andReturn($browser)
        ;

        return $test;
    }
}
