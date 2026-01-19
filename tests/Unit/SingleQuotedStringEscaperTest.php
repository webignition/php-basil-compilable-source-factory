<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;

class SingleQuotedStringEscaperTest extends TestCase
{
    private SingleQuotedStringEscaper $escaper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->escaper = SingleQuotedStringEscaper::create();
    }

    #[DataProvider('escapeDataProvider')]
    public function testEscape(string $string, string $expectedEscapedString): void
    {
        $this->assertEquals($expectedEscapedString, $this->escaper->escape($string));
    }

    /**
     * @return array<mixed>
     */
    public static function escapeDataProvider(): array
    {
        return [
            'no single quotes' => [
                'string' => 'value',
                'expectedEscapedString' => 'value',
            ],
            'encapsulated in single quotes' => [
                'string' => "'value'",
                'expectedEscapedString' => "\\'value\\'",
            ],
            'contains single quotes' => [
                'string' => "va'lu'e",
                'expectedEscapedString' => "va\\'lu\\'e",
            ],
            'escaped single quotes' => [
                'string' => "\\'value\\'",
                'expectedEscapedString' => "\\\\\\'value\\\\\\'",
            ],
            'contains single quotes for json_encoded data' => [
                'string' => '"va\\\'l\\\'ue"',
                'expectedEscapedString' => '"va\\\\\\\'l\\\\\\\'ue"',
            ],
        ];
    }
}
