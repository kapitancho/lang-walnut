<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;

interface FunctionContextFiller {

	public function fillValidationContext(
		ValidationContext $validationContext,
		NameAndType $target, NameAndType $parameter, NameAndType $dependency,
	): ValidationContext;

	public function fillExecutionContext(
		ExecutionContext $executionContext,
		NameAndType $target, Value|null $targetValue,
		NameAndType $parameter, Value|null $parameterValue,
		NameAndType $dependency, Value|null $dependencyValue,
	): ExecutionContext;

}