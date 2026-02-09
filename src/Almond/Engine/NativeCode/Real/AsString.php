<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class AsString extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, NullType $parameterType): StringType {
			$min = $targetType->numberRange->min;
			$max = $targetType->numberRange->max;
			$minLength = match(true) {
				$max !== PlusInfinity::value && $max->value <= 0 =>
				strlen((string)$max->value->sub($max->inclusive ? 0 : 1)),
				$min !== MinusInfinity::value && $min->value >= 0 =>
				strlen((string)$min->value->add($min->inclusive ? 0 : 1)),
				default => 1,
			};
			return $this->typeRegistry->string($minLength);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string((string)$target->literalValue);
	}

}