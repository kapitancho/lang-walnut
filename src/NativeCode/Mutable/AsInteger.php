<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsInteger implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MutableType) {
			$valueType = $targetType->valueType;
			$method = $programRegistry->methodFinder->methodForType(
				$valueType,
				new MethodNameIdentifier('asInteger')
			);
			if ($method instanceof Method) {
				return $method->analyse($programRegistry, $valueType, $parameterType);
			}
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

		if ($targetValue instanceof MutableValue) {
			$value = $targetValue->value;
			$method = $programRegistry->methodFinder->methodForType(
				$targetValue->targetType,
				new MethodNameIdentifier('asInteger')
			);
			if ($method instanceof Method) {
				return $method->execute(
					$programRegistry,
					TypedValue::forValue($value)->withType($targetValue->targetType),
					$parameter
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}