<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, RecordValue> */
final readonly class Reduce extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ArrayType $targetType */
		$expectedType = $this->typeRegistry->record([
			'reducer' => $this->typeRegistry->function(
				$this->typeRegistry->record([
					'result' => $this->typeRegistry->nothing,
					'item' => $targetType->itemType,
				], null),
				$this->typeRegistry->any
			),
			'initial' => $this->typeRegistry->any,
		], null);

		if (!$parameterType->isSubtypeOf($expectedType)) {
			return sprintf(
				"The parameter type %s is not a subtype of [reducer: ^[result: T, item:  %s] => T, initial: T]",
				$parameterType,
				$targetType->itemType
			);
		}

		[$initialType, $resultType, $reducerReturnReturnType] = $this->getAllTypes($parameterType);

		if (!$reducerReturnReturnType->isSubtypeOf($resultType)) {
			return sprintf(
				"[%s] Reducer return type %s must match result type %s",
				__CLASS__,
				$reducerReturnReturnType,
				$resultType
			);
		}
		if (!$initialType->isSubtypeOf($resultType)) {
			return sprintf(
				"[%s] Initial value type %s must match result type %s",
				__CLASS__,
				$initialType,
				$resultType
			);
		}
		return null;
	}

	/** @return array{Type, Type, Type, Type|null} */
	private function getAllTypes(Type $parameterType): array {
		/** @var RecordType $parameterType */
		$parameterType = $this->toBaseType($parameterType);
		/** @var FunctionType $reducerType */
		$reducerType = $this->toBaseType($parameterType->types['reducer']);
		$initialType = $this->toBaseType($parameterType->types['initial']);
		/** @var RecordType $reducerParamType */
		$reducerParamType = $this->toBaseType($reducerType->parameterType);
		$resultType = $reducerParamType->types['result'] ?? $this->typeRegistry->any;

		$reducerReturnType = $this->toBaseType($reducerType->returnType);
		$reducerReturnReturnType = $reducerReturnType instanceof ResultType ?
			$reducerReturnType->returnType : $reducerReturnType;
		$reducerReturnErrorType = $reducerReturnType instanceof ResultType ?
			$reducerReturnType->errorType : null;

		return [
			$initialType,
			$resultType,
			$reducerReturnReturnType,
			$reducerReturnErrorType,
		];

	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type {
			[,$resultType,, $reducerReturnErrorType] = $this->getAllTypes($parameterType);

			return $reducerReturnErrorType ?
				$this->typeRegistry->result($resultType, $reducerReturnErrorType) :
				$resultType;
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
