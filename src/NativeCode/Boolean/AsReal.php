<?php

namespace Walnut\Lang\NativeCode\Boolean;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsReal implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BooleanType) {
			return $programRegistry->typeRegistry->realSubset([
				new Number(0.0),
				new Number(1.0)
			]);
		}
		if ($targetType instanceof TrueType) {
			return $programRegistry->typeRegistry->realSubset([
				new Number(1.0)
			]);
		}
		if ($targetType instanceof FalseType) {
			return $programRegistry->typeRegistry->realSubset([
				new Number(0.0),
			]);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof BooleanValue) {
			$target = $targetValue->literalValue;
			return TypedValue::forValue($programRegistry->valueRegistry->real($target ? 1.0 : 0.0));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}