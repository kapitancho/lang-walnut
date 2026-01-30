<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface MethodContext extends MethodFinder {
	public function validateMethod(
		Type $targetType, MethodName $methodName, Type $parameterType, Expression|null $origin
	): ValidationSuccess|ValidationFailure;

	public function validateCast(
		Type $targetType, TypeName $castTypeName, Expression|null $origin
	): ValidationSuccess|ValidationFailure;

	/** @throws ExecutionException */
	public function executeMethod(Value $target, MethodName $methodName, Value $parameter): Value;

	/** @throws ExecutionException */
	public function executeCast(Value $target, TypeName $castTypeName): Value;
}