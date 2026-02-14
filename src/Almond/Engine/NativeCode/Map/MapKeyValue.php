<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
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

/** @extends NativeMethod<MapType|RecordType, FunctionType, RecordValue, FunctionValue> */
final readonly class MapKeyValue extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof MapType || $targetType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				$callbackParameterType = $parameterType->parameterType;
				$expectedType = $this->typeRegistry->record([
					'key' => $type->keyType,
					'value' => $type->itemType
				], null);
				if ($expectedType->isSubtypeOf($callbackParameterType)) {
					$r = $parameterType->returnType;
					$errorType = $r instanceof ResultType ? $r->errorType : null;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;
					$t = $this->typeRegistry->map(
						$returnType,
						$type->range->minLength,
						$type->range->maxLength,
						$type->keyType
					);
					return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$expectedType,
						$callbackParameterType
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
			foreach($target->values as $key => $value) {
				$r = $parameter->execute(
					$this->valueRegistry->record([
						'key' => $this->valueRegistry->string($key),
						'value' => $value
					])
				);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				$result[$key] = $r;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
