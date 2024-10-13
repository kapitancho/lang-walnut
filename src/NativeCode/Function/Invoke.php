<?php

namespace Walnut\Lang\NativeCode\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Invoke implements NativeMethod {
	use BaseType;
	use TupleAsRecord;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(Type $targetType, Type $parameterType): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof FunctionType) {
			if (!(
				$parameterType->isSubtypeOf($targetType->parameterType()) || (
					$targetType->parameterType() instanceof RecordType &&
					$parameterType instanceof TupleType &&
					$this->isTupleCompatibleToRecord(
						$this->context->typeRegistry(),
						$parameterType,
						$targetType->parameterType()
					)
				)
			)) {
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("Invalid parameter type: %s, %s expected",
					$parameterType,
					$targetType->parameterType()
				));
				// @codeCoverageIgnoreEnd
			}
			return $targetType->returnType();
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(
			sprintf("Invalid target type: %s, expected a function", $targetType)
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof FunctionValue) {
			if ($parameterValue instanceof TupleValue && $targetValue->parameterType() instanceof RecordType) {
				$parameterValue = $this->getTupleAsRecord(
					$this->context->valueRegistry(),
					$parameterValue,
					$targetValue->parameterType(),
				);
			}
			return new TypedValue(
				$targetValue->returnType(),
				$targetValue->execute(
					$this->context->globalContext(),
					$parameterValue
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}