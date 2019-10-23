<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class PagePropertyTranspiler implements HandlerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';

    private $variableDependencies;
    private $transpiledValueMap;

    public function __construct()
    {
        $this->variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $this->variableDependencies->create(VariableNames::PANTHER_CLIENT);
        $pantherClientPlaceholderAsString = (string) $pantherClientPlaceholder;

        $this->transpiledValueMap = [
            self::PROPERTY_NAME_TITLE => $pantherClientPlaceholderAsString . '->getTitle()',
            self::PROPERTY_NAME_URL => $pantherClientPlaceholderAsString . '->getCurrentURL()',
        ];
    }

    public static function createTranspiler(): PagePropertyTranspiler
    {
        return new PagePropertyTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::PAGE_PROPERTY === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function transpile(object $model): SourceInterface
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $transpiledValue = $this->transpiledValueMap[$model->getProperty()] ?? null;

            if (is_string($transpiledValue)) {
                $metadata = (new Metadata())
                    ->withVariableDependencies($this->variableDependencies);

                return (new Source())
                    ->withStatements([(string) $transpiledValue])
                    ->withMetadata($metadata);
            }

            throw new UnknownObjectPropertyException($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
