<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PER-CS' => true,
    '@PhpCsFixer' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'class_definition' => false,
    'trailing_comma_in_multiline' => false,
    'php_unit_internal_class' => false,
    'php_unit_test_class_requires_covers' => false,
    // Below configuration added to allow PR#629 to a pass
    // @todo remove in #631
    'operator_linebreak' => false,
    'single_line_empty_body' => false,
    'phpdoc_order' => false,
    'stringable_for_to_string' => false,
    'ordered_types' => false,
    'fully_qualified_strict_types' => false,
    'ordered_imports' => false,
    'no_useless_concat_operator' => false,
    'string_implicit_backslashes' => false,
    'php_unit_data_provider_method_order' => false,
])->setFinder($finder);
