<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, FunctionType, RecordValue, FunctionValue> */
final readonly class Partition extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof MapType || $targetType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean)) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					$partitionType = $this->typeRegistry->map($type->itemType, 0, $type->range->maxLength);
					return $this->typeRegistry->record([
						'matching' => $partitionType,
						'notMatching' => $partitionType
					], null);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$type->itemType,
						$parameterType->parameterType
					),
					$origin
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): RecordValue {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;

			foreach($target->values as $k => $value) {
				$r = $parameter->execute($value);
				if ($true->equals($r)) {
					$matching[$k] = $value;
				} else {
					$notMatching[$k] = $value;
				}
			}

			return $this->valueRegistry->record([
				'matching' => $this->valueRegistry->record($matching),
				'notMatching' => $this->valueRegistry->record($notMatching)
			]);
		};
	}

}
