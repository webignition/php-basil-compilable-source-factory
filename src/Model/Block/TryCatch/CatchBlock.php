<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block\TryCatch;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;

class CatchBlock extends AbstractBlock
{
    private const RENDER_TEMPLATE = <<<'EOD'
catch ({{ class_list }} {{ variable }}) {
{{ body }}
}
EOD;

    private ObjectTypeDeclarationCollection $caughtClasses;

    public function __construct(
        ObjectTypeDeclarationCollection $caughtClasses,
        BodyInterface $body
    ) {
        parent::__construct($body);

        $this->caughtClasses = $caughtClasses;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'class_list' => $this->caughtClasses,
            'variable' => Property::asObjectVariable('exception'),
            'body' => $this->createResolvableBody(),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = parent::getMetadata();

        return $metadata->merge($this->caughtClasses->getMetadata());
    }
}
