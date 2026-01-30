<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope;

interface ValidationContext extends ValidationResult {
	public VariableScope $variableScope { get; }
	public Type $expressionType { get; }
	public Type $returnType { get; }

	public function withAddedVariableTypes(iterable $types): ValidationContext;
	public function withAddedVariableType(VariableName $name, Type $type): ValidationContext;
	public function withExpressionType(Type $type): ValidationContext;
	public function withReturnType(Type $type): ValidationContext;

	/** @var array{} $errors */
	public array $errors { get; }

	public function hasErrors(): false;
}