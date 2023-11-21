<?php

declare(strict_types=1);

return static function ( bool $for_tests = false ): array {
	$rules = [
		'array_push' => true,
		'mb_str_functions' => true,
		'no_alias_functions' => true,
		'no_alias_language_construct_call' => true,
		'no_mixed_echo_print' => true,
		'pow_to_exponentiation' => true,
		'random_api_migration' => true,
		'set_type_to_cast' => true,
		'array_syntax' => true,
		'no_multiline_whitespace_around_double_arrow' => true,
		'no_trailing_comma_in_singleline_array' => true,
		'no_whitespace_before_comma_in_array' => true,
		'normalize_index_brace' => true,
		'whitespace_after_comma_in_array' => true,
		'braces' => [
			'position_after_functions_and_oop_constructs' => 'same',
		],
		'encoding' => true,
		'non_printable_character' => true,
		'constant_case' => true,
		'lowercase_keywords' => true,
		'lowercase_static_reference' => true,
		'magic_constant_casing' => true,
		'magic_method_casing' => true,
		'native_function_casing' => true,
		'native_function_type_declaration_casing' => true,
		'cast_spaces' => true,
		'lowercase_cast' => true,
		'modernize_types_casting' => true,
		'no_short_bool_cast' => true,
		'no_unset_cast' => true,
		'short_scalar_cast' => true,
		'class_attributes_separation' => true,
		'class_definition' => true,
		'final_internal_class' => true,
		'no_blank_lines_after_class_opening' => true,
		'no_null_property_initialization' => true,
		'no_php4_constructor' => true,
		'no_unneeded_final_method' => true,
		'ordered_interfaces' => true,
		'ordered_traits' => true,
		'protected_to_private' => true,
		'self_accessor' => true,
		'self_static_accessor' => true,
		'single_class_element_per_statement' => true,
		'single_trait_insert_per_statement' => true,
		'visibility_required' => true,
		'multiline_comment_opening_closing' => true,
		'no_empty_comment' => true,
		'no_trailing_whitespace_in_comment' => true,
		'single_line_comment_style' => true,
		'native_constant_invocation' => true,
		'elseif' => true,
		'include' => true,
		'no_break_comment' => true,
		'no_superfluous_elseif' => true,
		'no_trailing_comma_in_list_call' => true,
		'no_unneeded_control_parentheses' => true,
		'no_unneeded_curly_braces' => true,
		'no_useless_else' => true,
		'simplified_if_return' => true,
		'switch_case_semicolon_to_colon' => true,
		'switch_case_space' => true,
		'switch_continue_to_break' => true,
		'trailing_comma_in_multiline' => [
			'elements' => ['arrays'],
		],
		'yoda_style' => true,
		// @todo This removes spacing within function signature.
		// 'function_declaration' => [
		// 	'closure_function_spacing' => 'none',
		// ],
		'function_typehint_space' => true,
		'implode_call' => true,
		'lambda_not_used_import' => true,
		'method_argument_space' => true,
		'native_function_invocation' => [
			'include' => ['@all'],
			'exclude' => ['add_command', 'array_get', 'array_has', 'array_only', 'array_set'],
		],
		'no_spaces_after_function_name' => true,
		'no_unreachable_default_argument_value' => true,
		'no_useless_sprintf' => true,
		'nullable_type_declaration_for_default_null_value' => true,
		'regular_callable_call' => true,
		'return_type_declaration' => true,
		'void_return' => true,
		'fully_qualified_strict_types' => true,
		'global_namespace_import' => true,
		'no_leading_import_slash' => true,
		'no_unused_imports' => true,
		'ordered_imports' => [
			'imports_order' => ['const', 'class', 'function'],
		],
		'single_import_per_statement' => true,
		'single_line_after_imports' => true,
		'combine_consecutive_issets' => true,
		'combine_consecutive_unsets' => true,
		'declare_equal_normalize' => true,
		'declare_parentheses' => true,
		'dir_constant' => true,
		'explicit_indirect_variable' => true,
		'function_to_constant' => true,
		'is_null' => true,
		'no_unset_on_property' => true,
		'single_space_after_construct' => true,
		'list_syntax' => true,
		'blank_line_after_namespace' => true,
		'clean_namespace' => true,
		'no_leading_namespace_whitespace' => true,
		'single_blank_line_before_namespace' => true,
		'binary_operator_spaces' => true,
		'concat_space' => [
			'spacing' => 'one',
		],
		'logical_operators' => true,
		'new_with_braces' => true,
		'not_operator_with_space' => true,
		'object_operator_without_whitespace' => true,
		'operator_linebreak' => true,
		'standardize_increment' => true,
		'standardize_not_equals' => true,
		'ternary_operator_spaces' => true,
		'ternary_to_elvis_operator' => true,
		'ternary_to_null_coalescing' => true,
		'unary_operator_spaces' => true,
		'blank_line_after_opening_tag' => true,
		'echo_tag_syntax' => true,
		'full_opening_tag' => true,
		'linebreak_after_opening_tag' => true,
		'no_closing_tag' => true,
		// @todo PHPUnit rules?
		'align_multiline_comment' => [
			'comment_type' => 'phpdocs_like',
		],
		'no_blank_lines_after_phpdoc' => true,
		'no_empty_phpdoc' => true,
		'no_superfluous_phpdoc_tags' => true,
		'phpdoc_add_missing_param_annotation' => true,
		'phpdoc_align' => true,
		'phpdoc_annotation_without_dot' => true,
		'phpdoc_indent' => true,
		'phpdoc_line_span' => true,
		'phpdoc_no_alias_tag' => true,
		'phpdoc_no_empty_return' => true,
		'phpdoc_order' => true,
		'phpdoc_return_self_reference' => true,
		'phpdoc_scalar' => true,
		'phpdoc_separation' => true,
		'phpdoc_single_line_var_spacing' => true,
		'phpdoc_summary' => true,
		'phpdoc_to_comment' => [
			'ignored_tags' => ['psalm-suppress'],
		],
		'phpdoc_trim_consecutive_blank_line_separation' => true,
		'phpdoc_trim' => true,
		'phpdoc_types' => true,
		'phpdoc_types_order' => true,
		'phpdoc_var_annotation_correct_order' => true,
		'phpdoc_var_without_name' => true,
		'no_useless_return' => true,
		'return_assignment' => true,
		'multiline_whitespace_before_semicolons' => true,
		'no_empty_statement' => true,
		'no_singleline_whitespace_before_semicolons' => true,
		'semicolon_after_instruction' => true,
		'space_after_semicolon' => true,
		'declare_strict_types' => true,
		'strict_comparison' => true,
		'strict_param' => true,
		'escape_implicit_backslashes' => true,
		'explicit_string_variable' => true,
		'heredoc_to_nowdoc' => true,
		'no_binary_string' => true,
		'no_trailing_whitespace_in_string' => true,
		'simple_to_complex_string_variable' => true,
		'single_quote' => true,
		'string_line_ending' => true,
		'array_indentation' => true,
		'blank_line_before_statement' => true,
		'compact_nullable_typehint' => true,
		'indentation_type' => true,
		'line_ending' => true,
		'method_chaining_indentation' => true,
		'no_extra_blank_lines' => true,
		// @todo Verify literals vs variables.
		// 'no_spaces_around_offset' => true,
		'no_trailing_whitespace' => true,
		'no_whitespace_in_blank_line' => true,
		'single_blank_line_at_eof' => true,
	];

    if ( ! $for_tests ) {
        $rules = $rules + [
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'property_public_static',
                    'property_protected_static',
                    'property_private_static',
                    'construct',
                    'destruct',
                    'magic',
                    'phpunit',
                    'method_public',
                    'method_protected',
                    'method_private',
                    'method_public_static',
                    'method_protected_static',
                    'method_private_static',
                ],
                'sort_algorithm' => 'alpha',
            ],
            'static_lambda' => true,
            'simplified_null_return' => true,
        ];
    }

    return $rules;
};