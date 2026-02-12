<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, NullType, NullValue> */
final readonly class AsJsonValue extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, NullType $parameterType, mixed $origin): ValidationSuccess|ValidationFailure =>
			$this->methodContext->validateMethod(
				$targetType->itemType,
				new MethodName('asJsonValue'),
				$parameterType,
				$origin
			);
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, NullValue $parameter): Value {
			$result = [];
			foreach($target->values as $key => $value) {
				$returnValue = $this->methodContext->executeMethod(
					$value,
					new MethodName('asJsonValue'),
					$parameter
				);
				if ($returnValue instanceof ErrorValue) {
					return $returnValue;
				}
				$result[$key] = $returnValue;
			}
			return $this->valueRegistry->record($result);
		};
	}

}