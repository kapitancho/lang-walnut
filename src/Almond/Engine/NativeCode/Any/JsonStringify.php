<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, NullType, Value, NullValue> */
final readonly class JsonStringify extends NativeMethod {

	protected function getValidator(): callable {
		return function(Type $targetType, NullType $parameterType): Type {
			$resultType = $this->typeRegistry->string();
			return $targetType->isSubtypeOf($this->typeRegistry->core->jsonValue)
				? $resultType
				: $this->typeRegistry->result(
					$resultType,
					$this->typeRegistry->core->invalidJsonValue
				);
		};
	}

	protected function getExecutor(): callable {
		return function(Value $target, NullValue $parameter): Value {
			$step1 = $this->methodContext->executeMethod(
				$target,
				new MethodName('asJsonValue'),
				$parameter
			);
			if ($step1 instanceof ErrorValue) {
				return $step1;
			}
			return $this->methodContext->executeMethod(
				$step1,
				new MethodName('stringify'),
				$parameter
			);
		};
	}

}
