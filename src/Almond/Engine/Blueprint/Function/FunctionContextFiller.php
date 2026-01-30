<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

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