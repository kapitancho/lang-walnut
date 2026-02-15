<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class Partition extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf(
				$this->typeRegistry->result($this->typeRegistry->boolean, $this->typeRegistry->any)
			)) {
				$pType = $this->toBaseType($parameterType->returnType);
				if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
					$partitionType = $this->typeRegistry->array(
						$targetType->itemType,
						0,
						$targetType->range->maxLength
					);
					$returnType = $this->typeRegistry->record([
						'matching' => $partitionType,
						'notMatching' => $partitionType,
					], null);
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
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;
			foreach ($target->values as $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($true->equals($r)) {
					$matching[] = $value;
				} else {
					$notMatching[] = $value;
				}
			}
			return $this->valueRegistry->record([
				'matching' => $this->valueRegistry->tuple(array_values($matching)),
				'notMatching' => $this->valueRegistry->tuple(array_values($notMatching)),
			]);
		};
	}

}
