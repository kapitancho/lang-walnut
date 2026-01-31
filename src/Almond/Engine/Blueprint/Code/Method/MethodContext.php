<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

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