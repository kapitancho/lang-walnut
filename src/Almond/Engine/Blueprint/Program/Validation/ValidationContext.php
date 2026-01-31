<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;

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