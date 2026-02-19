<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<AnyType, FunctionType, FunctionValue> */
final readonly class FilterKeyValue extends MapNativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		/** @var FunctionType $parameterType */
		return $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean);
	}

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, mixed $origin): MapType|ValidationFailure {
			$kv = $this->typeRegistry->record([
				'key' => $targetType->keyType,
				'value' => $targetType->itemType
			], null);

			if ($kv->isSubtypeOf($parameterType->parameterType)) {
				return $this->typeRegistry->map(
					$targetType->itemType,
					0,
					$targetType->range->maxLength,
					$targetType->keyType
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"The parameter type %s of the callback function is not a subtype of %s",
					$parameterType->parameterType,
					$kv,
				),
				origin: $origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): Value {
			$values = $target->values;
			$result = [];
			$true = $this->valueRegistry->true;
			foreach($values as $key => $value) {
				$filterResult = $parameter->execute(
					$this->valueRegistry->record([
						'key' => $this->valueRegistry->string($key),
						'value' => $value
					])
				);
				if ($filterResult->equals($true)) {
					$result[$key] = $value;
				}
			}
			return $this->valueRegistry->record($result);
		};
	}

}
