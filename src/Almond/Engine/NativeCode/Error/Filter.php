<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ResultType, NullType, ErrorValue, NullValue> */
final readonly class Filter extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var FunctionType $parameterType */
		return $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean) ?
			null : 'The parameter of filter must be a function that returns a Boolean.';
	}

	protected function getValidator(): callable {
		return fn(ErrorType $targetType, FunctionType $parameterType, mixed $origin): ErrorType => $targetType;
	}

	protected function getExecutor(): callable {
		return fn(ErrorValue $target, Value $parameter): ErrorValue => $target;
	}

}
