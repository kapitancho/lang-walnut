<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, FunctionType, TupleValue, FunctionValue> */
final readonly class MapIndexValue extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				$callbackParameterType = $parameterType->parameterType;
				$expectedType = $this->typeRegistry->record([
					'index' => $this->typeRegistry->integer(0,
						$type->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
							max($type->range->maxLength - 1, 0)
					),
					'value' => $type->itemType
				], null);
				if ($expectedType->isSubtypeOf($callbackParameterType)) {
					$r = $parameterType->returnType;
					$errorType = $r instanceof ResultType ? $r->errorType : null;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;
					$t = $this->typeRegistry->array(
						$returnType,
						$type->range->minLength,
						$type->range->maxLength,
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
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$result = [];
			foreach ($target->values as $index => $value) {
				$r = $parameter->execute(
					$this->valueRegistry->record([
						'index' => $this->valueRegistry->integer($index),
						'value' => $value
					])
				);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				$result[] = $r;
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}
