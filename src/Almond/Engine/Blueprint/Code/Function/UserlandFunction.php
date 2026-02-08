<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope;

interface UserlandFunction {

	public NameAndType $target { get; }
	public NameAndType $parameter { get; }
	public NameAndType $dependency { get; }
	public Type $returnType { get; }
	public FunctionBody $functionBody { get; }

	public function validateInVariableScope(VariableScope $variableScope): ValidationSuccess|ValidationFailure;
	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure;

	/** @throws ExecutionException */
	public function execute(
		VariableValueScope $variableValueScope, Value|null $targetValue, Value $parameterValue
	): Value;

}