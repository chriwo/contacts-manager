<?php

use TYPO3\CodingStandards\CsFixerConfig;

$csFixerConfig = CsFixerConfig::create();
$csFixerConfig
    ->getFinder()
    ->in([
        __DIR__,
    ])
    ->exclude([
        '.build',
        '.ddev',
        '.github',
        'config',
        'var',
    ]);

// replace deprecated rules
$replacements = [
    '@PER-CS1.0' => '@PER-CS1x0',
];
$rules = $csFixerConfig->getRules();
foreach ($replacements as $deprecatedRule => $replacementRule) {
    if (array_key_exists($deprecatedRule, $rules)) {
        $deprecatedRuleValue = $rules[$deprecatedRule];
        $rules[$replacementRule] = $deprecatedRuleValue;
        unset($rules[$deprecatedRule]);
    }
}

$csFixerConfig->setRules(array_replace_recursive(
    $rules,
    [
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
            'leading_backslash_in_global_namespace' => false,
        ],
        'global_namespace_import' => false,
        'header_comment' => ['header' => ''],
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_line_empty_body' => false,
        'no_trailing_comma_in_singleline' => true,
        'php_unit_test_annotation' => ['style' => 'annotation'],
    ]
));

return $csFixerConfig;
