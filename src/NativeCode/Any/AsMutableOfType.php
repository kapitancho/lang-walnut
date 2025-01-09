<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class AsMutableOfType implements NativeMethod {
	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($parameterType instanceof TypeType) {
			return $programRegistry->typeRegistry->result(
				//$programRegistry->typeRegistry->type(
					$programRegistry->typeRegistry->metaType(MetaTypeValue::MutableType)
				/*)*/,
				$programRegistry->typeRegistry->sealed(new TypeNameIdentifier("CastNotAvailable"))
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		if ($parameterValue instanceof TypeValue) {
			if ($targetValue->type->isSubtypeOf($parameterValue->typeValue)) {
				return TypedValue::forValue($programRegistry->valueRegistry->mutable(
					$parameterValue->typeValue,
					$targetValue
				));
			}
			return TypedValue::forValue($programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->sealedValue(
					new TypeNameIdentifier("CastNotAvailable"),
					$programRegistry->valueRegistry->record([
						'from' => $programRegistry->valueRegistry->type($targetValue->type),
						'to' => $programRegistry->valueRegistry->type($parameterValue->typeValue)
					])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}