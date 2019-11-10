<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class WaitForActionHandler
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createHandler(): WaitForActionHandler
    {
        return new WaitForActionHandler(SingleQuotedStringEscaper::create());
    }

    /**
     * @param InteractionActionInterface $action
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(InteractionActionInterface $action): BlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new UnsupportedModelException($action);
        }

        $elementLocator = $identifier->getLocator();

        if ('/' === $elementLocator[0]) {
            throw new UnsupportedModelException($action);
        }

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        return new Block([
            new Statement(
                sprintf(
                    '%s = %s->waitFor(\'%s\')',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $this->singleQuotedStringEscaper->escape($elementLocator)
                ),
                $metadata
            )
        ]);
    }
}
