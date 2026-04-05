<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Empty;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EmptyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ResultType, NullType, EmptyValue, NullValue> */
final readonly class Filter extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var FunctionType $parameterType */
		return $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean) ?
			null : 'The parameter of filter must be a function that returns a Boolean.';
	}

	protected function getValidator(): callable {
		return fn(EmptyType $targetType, FunctionType $parameterType, mixed $origin): EmptyType => $targetType;
	}

	protected function getExecutor(): callable {
		return fn(EmptyValue $target, Value $parameter): EmptyValue => $target;
	}

}
