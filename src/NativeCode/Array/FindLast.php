<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FindLast implements NativeMethod {
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
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($this->context->typeRegistry->boolean)) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					return $this->context->typeRegistry->result(
						$type->itemType,
						$this->context->typeRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
					);
				}
				throw new AnalyserException(
					"The parameter type %s of the callback function is not a subtype of %s",
					$type->itemType,
					$parameterType->parameterType
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
			$values = $targetValue->values;
			$true = $this->context->valueRegistry->true;
			for ($index = count($values) - 1; $index >= 0; $index--) {
				$r = $parameterValue->execute($this->context->globalContext, $values[$index]);
				if ($true->equals($r)) {
					return $values[$index];
				}
			}
			return TypedValue::forValue(
				$this->context->valueRegistry->error(
					$this->context->valueRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}