<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        '@PHP70Migration' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        //'declare_strict_types' => true,
        'heredoc_to_nowdoc' => true,
        'is_null' => [
            'use_yoda_style' => false,
        ],
        'linebreak_after_opening_tag' => true,
        'new_with_braces' => false,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_add_missing_param_annotation' => false,
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => true,
        'pow_to_exponentiation' => true,
        'unary_operator_spaces' => false,
        'return_type_declaration' => [
            'space_before' => 'one',
        ],
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'yoda_style' => false,
        'native_function_invocation' => false,
    ])
    ->setFinder($finder);
