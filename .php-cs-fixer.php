<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config
    ->getFinder()
    ->in([
        __DIR__ . '/',
    ])
    ->exclude([
        '.build',
        '.ddev',
        '.project',
    ])
;

$deprecatedRulesWithReplacement = [
    'braces' => '',
    'compact_nullable_typehint' => 'compact_nullable_type_declaration',
    'function_typehint_space' => 'type_declaration_spaces',
    'new_with_braces' => 'new_with_parentheses',
    'no_trailing_comma_in_singleline_array' => 'no_trailing_comma_in_singleline',
];

$defaultRules = $config->getRules();
foreach ($deprecatedRulesWithReplacement as $deprecatedRule => $replacementRule) {
    if (isset($defaultRules[$deprecatedRule])) {
        if (!empty($replacementRule)) {
            $defaultRules[$replacementRule] =$defaultRules[$deprecatedRule];
        }

        unset($defaultRules[$deprecatedRule]);
    }
}

// add own rule configuration
$customRules = [
    'blank_line_after_namespace' => true,
    'declare_strict_types' => false,
    'no_blank_lines_after_class_opening' => true,
    'phpdoc_to_param_type' => true,
    'phpdoc_to_property_type' => true,
    'phpdoc_to_return_type' => true,
    'blank_lines_before_namespace' => true,
];

$config->setRules(array_merge_recursive($defaultRules, $customRules));

return $config;
