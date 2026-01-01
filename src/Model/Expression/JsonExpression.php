<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Json\LiteralInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

readonly class JsonExpression implements ExpressionInterface, \JsonSerializable
{
    public const string TEMPLATE_PLACEHOLDER = 'serialized_json';

    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getTemplate(): string
    {
        return '\'{{ ' . self::TEMPLATE_PLACEHOLDER . ' }}\'';
    }

    public function getContext(): array
    {
        $dataSet = $this->createDataSet($this->data);

        $serialized = (string) json_encode($dataSet['data'], JSON_PRETTY_PRINT);
        $serialized = addcslashes($serialized, "'");

        $serialized = $this->replacePlaceholdersWithLiteralValues(
            $this->data,
            $dataSet['placeholders'],
            $serialized
        );

        return [
            self::TEMPLATE_PLACEHOLDER => $serialized,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * @param array<mixed> $rawData
     *
     * @return array{placeholders: array<mixed>, data: array<mixed>}
     */
    private function createDataSet(array $rawData): array
    {
        $data = [];
        $literalPlaceholders = [];

        foreach ($rawData as $key => $value) {
            if ($value instanceof LiteralInterface) {
                $placeholder = md5((string) rand());

                $literalPlaceholders[$key] = $placeholder;
                $data[$key] = $placeholder;
            }

            if (is_array($value)) {
                $subDataSet = $this->createDataSet($value);

                $literalPlaceholders[$key] = $subDataSet['placeholders'];
                $data[$key] = $subDataSet['data'];
            }

            if (is_scalar($value) || $value instanceof JsonExpression) {
                $data[$key] = $value;
            }
        }

        return [
            'placeholders' => $literalPlaceholders,
            'data' => $data,
        ];
    }

    /**
     * @param array<mixed> $source
     * @param array<mixed> $placeholders
     */
    private function replacePlaceholdersWithLiteralValues(array $source, array $placeholders, string $output): string
    {
        foreach ($placeholders as $key => $placeholder) {
            if (is_string($placeholder)) {
                $literal = $source[$key];

                if ($literal instanceof LiteralInterface) {
                    $search = '"' . $placeholder . '"';

                    $replace = sprintf(
                        "%s' . %s . '%s",
                        $literal->isQuotable() ? '"' : '',
                        $literal->getValue(),
                        $literal->isQuotable() ? '"' : '',
                    );

                    $output = str_replace($search, $replace, $output);
                }
            }

            if (is_array($placeholder) && is_array($source[$key])) {
                $output = $this->replacePlaceholdersWithLiteralValues($source[$key], $placeholder, $output);
            }
        }

        return $output;
    }
}
