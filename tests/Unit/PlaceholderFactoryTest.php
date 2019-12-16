<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\PlaceholderFactory;

class PlaceholderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PlaceholderFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = PlaceholderFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $content, string $placeholderContent, string $expectedMutatedContent): void
    {
        $mutatedContent = $this->factory->create($content, $placeholderContent);

        $this->assertSame($expectedMutatedContent, $mutatedContent);
    }

    public function createDataProvider(): array
    {
        $placeholderContent = 'PLACEHOLDER';

        return [
            'content does not contain placeholder' => [
                'content' => 'this does not contain placeholder',
                'placeholderContent' => $placeholderContent,
                'expectedMutatedContent' => '{{ '  . $placeholderContent . ' }}',
            ],
            'content contains placeholder (1)' => [
                'content' => 'this does not contain {{ '  . $placeholderContent . ' }}',
                'placeholderContent' => $placeholderContent,
                'expectedMutatedContent' => '{{ '  . $placeholderContent . '1 }}',
            ],
            'content contains placeholder (2)' => [
                'content' =>
                    'this does not contain {{ '  . $placeholderContent . ' }} {{ '  . $placeholderContent . '1 }}',
                'placeholderContent' => $placeholderContent,
                'expectedMutatedContent' => '{{ '  . $placeholderContent . '2 }}',
            ],
        ];
    }
}
