<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, FunctionType, TupleValue, FunctionValue> */
final readonly class FlipMap extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if (!($targetType instanceof ArrayType || $targetType instanceof TupleType)) {
			return false;
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		return $type->itemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$itemType = $type->itemType;
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				if ($itemType->isSubtypeOf($parameterType->parameterType)) {
					$r = $parameterType->returnType;
					$errorType = $r instanceof ResultType ? $r->errorType : null;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;
					$t = $this->typeRegistry->map(
						$returnType,
						min(1, $type->range->minLength),
						$type->range->maxLength,
						$itemType
					);
					return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$itemType,
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
			$result = [];
			foreach ($target->values as $value) {
				/** @var StringValue $value */
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				$result[$value->literalValue] = $r;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
