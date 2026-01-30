<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope;

interface UserlandFunction {

	public NameAndType $target { get; }
	public NameAndType $parameter { get; }
	public NameAndType $dependency { get; }
	public Type $returnType { get; }
	public FunctionBody $functionBody { get; }

	public function validateInVariableScope(VariableScope $variableScope): ValidationSuccess|ValidationFailure;
	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;

	public function validate(Type $targetType, Type $parameterType): ValidationSuccess|ValidationFailure;

	/** @throws ExecutionException */
	public function execute(
		VariableValueScope $variableValueScope, Value|null $targetValue, Value $parameterValue
	): Value;

}