<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\ObjectReflector\ObjectReflector;

class SingleLineCommentTest extends AbstractResolvableTest
{
    public function testCreate(): void
    {
        $content = 'comment content';
        $comment = new SingleLineComment($content);

        $this->assertSame($content, ObjectReflector::getProperty($comment, 'content'));
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(SingleLineComment $comment, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $comment);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
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
