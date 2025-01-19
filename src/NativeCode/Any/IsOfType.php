<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class IsOfType implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($parameterType instanceof TypeType) {
			return $programRegistry->typeRegistry->boolean;
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	private function isSubtypeOf(Value $targetValue, TypeInterface $type): bool {
		return $targetValue->type->isSubtypeOf($type) ||
			($targetValue instanceof SubtypeValue && $this->isSubtypeOf($targetValue->baseValue, $type));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		if ($parameterValue instanceof TypeValue) {
			return TypedValue::forValue($programRegistry->valueRegistry->boolean(
				$this->isSubtypeOf($targetValue, $parameterValue->typeValue)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}