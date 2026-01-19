<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\ObjectReflector\ObjectReflector;

class SingleLineCommentTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $content = 'comment content';
        $comment = new SingleLineComment($content);

        $this->assertSame($content, ObjectReflector::getProperty($comment, 'content'));
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(SingleLineComment $comment, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $comment);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty' => [
                'comment' => new SingleLineComment(''),
                'expectedString' => '// ',
            ],
            'non-empty' => [
                'comment' => new SingleLineComment('non-empty'),
                'expectedString' => '// non-empty',
            ],
        ];
    }
}
