<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests'])
;
$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_before_statement' => [
            'statements' => ['continue', 'declare', 'return', 'throw', 'try', 'if', 'while', 'for'],
        ],
        'comment_to_phpdoc' => true,
        'compact_nullable_typehint' => true,
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => true,
        'function_to_constant' => [
            'functions' => ['get_class', 'get_called_class', 'php_sapi_name', 'phpversion', 'pi'],
        ],
        'heredoc_to_nowdoc' => true,
        'is_null' => true,
        'linebreak_after_opening_tag' => true,
        'list_syntax' => ['syntax' => 'short'],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'native_constant_invocation' => true,
        'native_function_invocation' => ['scope' => 'namespaced'],
        'no_alternative_syntax' => true,
        'no_binary_string' => true,
        'no_null_property_initialization' => true,
        'echo_tag_syntax' => ['format' => 'long'],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'no_singleline_whitespace_before_semicolons' => false,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unneeded_final_method' => true,
        'no_unreachable_default_argument_value' => true,
        'no_unset_on_property' => true,
        'no_useless_else' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_imports' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_mock' => true,
        'php_unit_namespaced' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false,
        'return_assignment' => true,
        'set_type_to_cast' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
