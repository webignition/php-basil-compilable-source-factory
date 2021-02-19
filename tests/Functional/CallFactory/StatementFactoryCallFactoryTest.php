<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\CallFactory\StatementFactoryCallFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\StatementInterface;
use webignition\BasilParser\AssertionParser;

class StatementFactoryCallFactoryTest extends AbstractBrowserTestCase
{
    private StatementFactoryCallFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = StatementFactoryCallFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $fixture, StatementInterface $statement, BodyInterface $teardownStatements): void
    {
        $source = $this->factory->create($statement);

        $variable = new VariableName('statement');
        $assignmentExpression = new AssignmentExpression($variable, $source);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            new Body([
                new Statement(
                    $assignmentExpression
                ),
            ]),
            $fixture,
            null,
            $teardownStatements
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);
        self::assertInstanceOf(TestRunJob::class, $testRunJob);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );
        }
    }

    /**
     * @return array[]
     */
    public function createDataProvider(): array
    {
        $assertionParser = AssertionParser::create();
        $singleQuotedStringEscaper = SingleQuotedStringEscaper::create();

        $assertion = $assertionParser->parse(
            '$"input[value=\"\'within single quotes\'\"]" is $"[name=input-with-single-quoted-value]"',
        );

        return [
            'statement arguments as json are correctly escaped' => [
                'fixture' => '/form.html',
                'statement' => $assertion,
                'teardownStatements' => new Body([
                    StatementFactory::createAssertInstanceOf('\'' . AssertionInterface::class . '\'', '$statement'),
                    StatementFactory::createAssertEquals(
                        '\'' . $singleQuotedStringEscaper->escape($assertion->getSource()) . '\'',
                        '$statement->getSource()'
                    ),
                ]),
            ],
        ];
    }
}
