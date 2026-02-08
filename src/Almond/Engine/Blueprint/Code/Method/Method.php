<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

interface Method {
	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure;

	/** @throws ExecutionException */
	public function execute(Value $target, Value $parameter): Value;
}