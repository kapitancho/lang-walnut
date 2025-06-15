<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
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
					$programRegistry->typeRegistry->metaType(MetaTypeValue::MutableValue)
				/*)*/,
				$programRegistry->typeRegistry->data(new TypeNameIdentifier("CastNotAvailable"))
			);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;

		if ($parameterValue instanceof TypeValue) {
			if ($target->type->isSubtypeOf($parameterValue->typeValue)) {
				return ($programRegistry->valueRegistry->mutable(
					$parameterValue->typeValue,
					$targetValue
				));
			}
			return ($programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->dataValue(
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