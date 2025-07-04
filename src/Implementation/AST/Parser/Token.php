<?php /** @noinspection SpellCheckingInspection */

namespace Walnut\Lang\Implementation\AST\Parser;

enum Token: string {
	case code_comment = '\/\*.*?\*\/';
	case dependency_marker = '\%\%';
	case function_body_marker = '\:\:';
	case cast_marker = '\=\=\>';
	case method_marker = '\-\>';
	case pure_marker = '\|\>';
	case rest_type = '\.\.\.';
	case range_dots = '\.\.';
	case not_equals = '\!\=';
	case equals = '\=\=';
	case expression_separator = '\;';
	case value_separator = '\,';
	case atom_type = '\:\(\)';
	case enum_type_start = '\:\(';
	case named_type = '\:\=';
	case colon = '\:';
	case type_proxy_keyword = '\\\\[A-Z][a-zA-Z0-9_]*';
	case boolean_op_not = '\!';
	case boolean_op = '(&&|\|\||\^\^)';
	case lambda_param = '\^';
	case lambda_return = '\=\>';
	case error_as_external = '\*\>';
	case call_start = '\(';
	case call_end = '\)';
	case sequence_start = '\{';
	case sequence_end = '\}';
	case less_than_equal = '\<\=';
	case greater_than_equal = '\>\=';
	case type_start = '\<';
	case type_end = '\>';
	case empty_tuple = '\[\]';
	case empty_record = '\[\:\]';
	case empty_set = '\[\;\]';
	case tuple_start = '\[';
	case tuple_end = '\]';
	case union = '\|';
	case intersection = '\&';
	case assign = '\=';
	case true = '\btrue\b';
	case false = '\bfalse\b';
	case null = '\bnull\b';
	case type = '\btype\b';
	case var = '\bvar\b';
	case mutable = '\bmutable\b';
	case no_error = '\?noError';
	case no_external_error = '\?noExternalError';
	case when_is_error = '\?whenIsError\b';
	case when_type_of = '\?whenTypeOf\b';
	case when_is_true = '\?whenIsTrue\b';
	case when_value_of = '\?whenValueOf\b';
	case when = '\?when\b';
	case when_value_is = '\bis\b';
	case optional_key = '\?\b';
	case string_value = '\'.*?\'';
	//TOOD: allow forward slash but not two consecutive forward slashes
	case module_identifier = 'module \$?[a-z][a-z0-9_-]*(\/[a-z][a-z0-9_-]*)*(\s*\%\%\s+\$?[a-z][a-z0-9_-]*(\/[a-z][a-z0-9_-]*)*(\s*\,\s*\$?[a-z][a-z0-9_-]*(\/[a-z][a-z0-9_-]*)*)*)?\:';
	case type_short = '`';
	case type_keyword = '[A-Z][a-zA-Z0-9_]*';
	case var_keyword = '[a-z][a-zA-Z0-9_]*';
	case special_var = '([\$\%\#][a-z_][a-zA-Z0-9_]*)|([\#\%\$][0-9]+)|(\$\$)';
	case special_var_param = '\#';
	case special_var_modulo = '\%';
	case this_var = '\$';
	case real_number = '(-0\.[0-9]*[1-9]+[0-9]*)|((0|(\-?[1-9][0-9]*))\.[0-9]+)';
	case positive_integer_number = '0|([1-9][0-9]*)';
	case integer_number = '0|(\-?[1-9][0-9]*)';
	case arithmetic_op2 = '(\*\*|\/\/)';
	case arithmetic_op_multiply = '\*';
	case arithmetic_op = '[\+\-\/]';
	case default_match = '\~';
	case property_accessor = '\.';
	case error_marker = '\@';
	case word = '[a-zA-Z]+';
}