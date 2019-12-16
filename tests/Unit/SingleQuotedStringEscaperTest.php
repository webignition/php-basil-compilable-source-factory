<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;

class SingleQuotedStringEscaperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SingleQuotedStringEscaper
     */
    private $escaper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->escaper = SingleQuotedStringEscaper::create();
    }

    /**
     * @dataProvider escapeDataProvider
     */
    public function testEscape(string $string, string $expectedEscapedString): void
    {
        $this->assertEquals($expectedEscapedString, $this->escaper->escape($string));
    }

    public function escapeDataProvider(): array
    {
        return [
            'no single quotes' => [
                'string' => 'value',
                'expectedEscapedString' => 'value',
            ],
            'encapsulated in single quotes' => [
                'string' => "'value'",
                'expectedEscapedString' => "\'value\'",
            ],
            'contains in single quotes' => [
                'string' => "va'lu'e",
                'expectedEscapedString' => "va\'lu\'e",
            ],
            'escaped single quotes' => [
                'string' => "\'value\'",
                'expectedEscapedString' => "\\\\\'value\\\\\'",
            ],
        ];
    }
}
