<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

// This method will not extend MapNativeMethod because it can return a RecordType
// instead of a MapType if the target type is a RecordType. In that case, the return type will be a RecordType
// with the same keys as the target type and the item type as the value type.
/** @extends NativeMethod<MapType|RecordType, FunctionType, RecordValue, FunctionValue> */
final readonly class Filter extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof MapType || $targetType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$recordReturnType = null;
			if ($targetType instanceof RecordType) {
				$recordReturnType = $this->typeRegistry->record(
					array_map(
						fn(Type $type): OptionalKeyType =>
						$type instanceof OptionalKeyType ?
							$type :
							$this->typeRegistry->optionalKey($type),
						$targetType->types
					),
					$targetType->restType
				);
				$targetType = $targetType->asMapType();
			}
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf(
				$this->typeRegistry->result($this->typeRegistry->boolean, $this->typeRegistry->any)
			)) {
				$pType = $this->toBaseType($parameterType->returnType);
				if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
					$returnType = $recordReturnType ?? $this->typeRegistry->map(
						$targetType->itemType,
						0,
						$targetType->range->maxLength,
						$targetType->keyType
					);
					return $pType instanceof ResultType ? $this->typeRegistry->result(
						$returnType,
						$pType->errorType
					) : $returnType;
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$targetType->itemType,
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
		return function(RecordValue $target, FunctionValue $parameter): Value {
			$result = [];
			$true = $this->valueRegistry->true;
			foreach($target->values as $key => $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($true->equals($r)) {
					$result[$key] = $value;
				}
			}
			return $this->valueRegistry->record($result);
		};
	}

}
