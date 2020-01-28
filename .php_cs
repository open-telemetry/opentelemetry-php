<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('var/cache')
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
	'concat_space' => ['spacing' => 'one'],
	'declare_equal_normalize' => ['space' => 'none'],
	'is_null' => true,
	'modernize_types_casting' => true,
	'ordered_imports' => true,
	'php_unit_construct' => true,
	'single_line_comment_style' => true,
	'yoda_style' => false,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_return' => true,
        'blank_line_before_statement' => true,
        'cast_spaces' => true,
        'declare_strict_types' => true,
        'function_typehint_space' => true,
        'include' => true,
        'lowercase_cast' => true,
        'new_with_braces' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_leading_import_slash' => true,
        'no_short_echo_tag' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_types' => true,
        'short_scalar_cast' => true,
        'single_blank_line_before_namespace' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline_array' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
