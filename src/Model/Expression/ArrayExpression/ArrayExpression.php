<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;

use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCreationTrait;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverEncapsulateWhenCastingTrait;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutationInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class ArrayExpression implements ExpressionInterface, ResolvedTemplateMutationInterface
{
    use DeferredResolvableCreationTrait;
    use IsNotStaticTrait;
    use NeverEncapsulateWhenCastingTrait;

    private const INDENT = '    ';

    /**
     * @var ArrayPair[]
     */
    private array $pairs;

    /**
     * @param ArrayPair[] $pairs
     */
    public function __construct(array $pairs, bool $renderKeys = true)
    {
        $mutatedPairs = [];
        foreach ($pairs as $pair) {
            if (false === $renderKeys) {
                $mutatedPairs[] = $pair->withoutRenderKey();
            } else {
                $mutatedPairs[] = $pair;
            }
        }

        $this->pairs = $mutatedPairs;
    }

    /**
     * @param array<mixed> $array
     */
    public static function fromArray(array $array, bool $renderKeys = true): self
    {
        $arrayPairs = [];

        foreach ($array as $key => $value) {
            $arrayPair = self::createArrayPair((string) $key, $value);
            if ($arrayPair instanceof ArrayPair) {
                $arrayPairs[] = $arrayPair;
            }
        }

        return new ArrayExpression($arrayPairs, $renderKeys);
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();
        array_walk($this->pairs, function (ArrayPair $pair) use (&$metadata) {
            $metadata = $metadata->merge($pair->getMetadata());
        });

        return $metadata;
    }

    /**
     * @return callable[]
     */
    public function getResolvedTemplateMutators(): array
    {
        return [
            function (string $resolvedTemplate) {
                $prefix = '[';
                $suffix = ']';

                if ('' !== $resolvedTemplate) {
                    $prefix .= "\n";
                }

                return $prefix . $resolvedTemplate . $suffix;
            },
        ];
    }

    public function mightThrow(): bool
    {
        foreach ($this->pairs as $pair) {
            if ($pair->getValue()->mightThrow()) {
                return true;
            }
        }

        return false;
    }

    public function getType(): TypeCollection
    {
        return TypeCollection::array();
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvablePairs = [];

        foreach ($this->pairs as $pair) {
            $resolvablePairs[] = new ResolvedTemplateMutatorResolvable(
                $pair,
                function (string $resolvedTemplate) {
                    return $this->arrayPairResolvedTemplateMutator($resolvedTemplate);
                }
            );
        }

        return ResolvableCollection::create($resolvablePairs);
    }

    private static function createArrayPair(string $key, mixed $value): ?ArrayPair
    {
        $valueExpression = self::createExpression($value);
        if ($valueExpression instanceof ExpressionInterface) {
            return new ArrayPair($key, $valueExpression);
        }

        return null;
    }

    private static function createExpression(mixed $value): ?ExpressionInterface
    {
        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if (is_scalar($value)) {
            if (is_string($value)) {
                $value = '\'' . $value . '\'';
            }

            return LiteralExpression::string((string) $value);
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        return null;
    }

    private function arrayPairResolvedTemplateMutator(string $resolved): string
    {
        $lines = explode("\n", $resolved);

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex > 0) {
                $lines[$lineIndex] = self::INDENT . $line;
            }
        }

        $resolved = implode("\n", $lines);

        return self::INDENT . $resolved . "\n";
    }
}
