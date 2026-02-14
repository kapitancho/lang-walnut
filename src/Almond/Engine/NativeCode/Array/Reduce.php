<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, Type, TupleValue, RecordValue> */
final readonly class Reduce extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->record([
					'reducer' => $this->typeRegistry->function(
						$this->typeRegistry->record([
							'result' => $this->typeRegistry->nothing,
							'item' => $type->itemType,
						], null),
						$this->typeRegistry->any
					),
					'initial' => $this->typeRegistry->any,
				], null)
			)) {
				$parameterType = $this->toBaseType($parameterType);
				$reducerType = $this->toBaseType($parameterType->types['reducer']);
				$initialType = $this->toBaseType($parameterType->types['initial']);

				$reducerParamType = $this->toBaseType($reducerType->parameterType);
				$resultType = $reducerParamType->types['result'] ?? $this->typeRegistry->any;

				$reducerReturnType = $this->toBaseType($reducerType->returnType);
				$reducerReturnErrorType = $reducerReturnType instanceof ResultType ?
					$reducerReturnType->errorType : null;
				$reducerReturnReturnType = $reducerReturnType instanceof ResultType ?
					$reducerReturnType->returnType : $reducerReturnType;

				if (!$reducerReturnReturnType->isSubtypeOf($resultType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"[%s] Reducer return type %s must match result type %s",
							__CLASS__,
							$reducerReturnReturnType,
							$resultType
						),
						$origin
					);
				}
				if (!$initialType->isSubtypeOf($resultType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"[%s] Initial value type %s must match result type %s",
							__CLASS__,
							$initialType,
							$resultType
						),
						$origin
					);
				}
				return $reducerReturnErrorType ?
					$this->typeRegistry->result($resultType, $reducerReturnErrorType) :
					$resultType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Parameter must be a record with 'reducer' and 'initial' fields", __CLASS__),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, RecordValue $parameter): Value {
			/** @var FunctionValue $reducer */
			$reducer = $parameter->values['reducer'];
			$accumulator = $parameter->values['initial'];

			foreach ($target->values as $item) {
				$reducerParam = $this->valueRegistry->record(['result' => $accumulator, 'item' => $item]);
				$accumulator = $reducer->execute($reducerParam);
				if ($accumulator instanceof ErrorValue) {
					break;
				}
			}
			return $accumulator;
		};
	}

}
