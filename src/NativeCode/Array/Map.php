<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Map implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				if ($type->itemType()->isSubtypeOf($parameterType->parameterType())) {
					$r = $parameterType->returnType();
					$errorType = $r instanceof ResultType ? $r->errorType() : null;
					$returnType = $r instanceof ResultType ? $r->returnType() : $r;
					$t = $this->context->typeRegistry()->array(
						$returnType,
						$type->range()->minLength(),
						$type->range()->maxLength(),
					);
					return $errorType ? $this->context->typeRegistry()->result($t, $errorType) : $t;
				}
				throw new AnalyserException(
					"The parameter type %s of the callback function is not a subtype of %s",
					$type->itemType(),
					$parameterType->parameterType()
				);
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue && $parameterValue instanceof FunctionValue) {
			$values = $targetValue->values();
			$result = [];
			foreach($values as $value) {
				$r = $parameterValue->execute($this->context->globalContext(), $value);
				if ($r instanceof ErrorValue) {
					return TypedValue::forValue($r);
				}
				$result[] = $r;
			}
			return TypedValue::forValue($this->context->valueRegistry()->tuple($result));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}