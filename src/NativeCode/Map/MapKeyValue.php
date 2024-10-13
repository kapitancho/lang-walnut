<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MapKeyValue implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($type instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				$callbackParameterType = $parameterType->parameterType();
				$expectedType = $this->context->typeRegistry()->record([
					'key' => $this->context->typeRegistry()->string(),
					'value' => $type->itemType()
				]);
				if ($expectedType->isSubtypeOf($callbackParameterType)) {
					$r = $parameterType->returnType();
					$errorType = $r instanceof ResultType ? $r->errorType() : null;
					$returnType = $r instanceof ResultType ? $r->returnType() : $r;
					$t = $this->context->typeRegistry()->map(
						$returnType,
						$type->range()->minLength(),
						$type->range()->maxLength(),
					);
					return $errorType ? $this->context->typeRegistry()->result($t, $errorType) : $t;
				}
				throw new AnalyserException(sprintf(
					"The parameter type %s of the callback function is not a subtype of %s",
					$expectedType,
					$callbackParameterType
				));
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
		if ($targetValue instanceof RecordValue && $parameterValue instanceof FunctionValue) {
			$values = $targetValue->values();
			$result = [];
			foreach($values as $key => $value) {
				$r = $parameterValue->execute(
					$this->context->globalContext(),
					$this->context->valueRegistry()->record([
						'key' => $this->context->valueRegistry()->string($key),
						'value' => $value
					])
				);
				$result[$key] = $r;
			}
			return TypedValue::forValue($this->context->valueRegistry()->record($result));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}