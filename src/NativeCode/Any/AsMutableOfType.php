<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class AsMutableOfType implements NativeMethod {
	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($parameterType instanceof TypeType) {
			return $typeRegistry->result(
				//$typeRegistry->type(
					$typeRegistry->metaType(MetaTypeValue::MutableValue)
				/*)*/,
				$typeRegistry->core->castNotAvailable
			);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($parameter instanceof TypeValue) {
			if ($target->type->isSubtypeOf($parameter->typeValue)) {
				return $programRegistry->valueRegistry->mutable(
					$parameter->typeValue,
					$target
				);
			}
			return $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->core->castNotAvailable(
					$programRegistry->valueRegistry->record([
						'from' => $programRegistry->valueRegistry->type($target->type),
						'to' => $programRegistry->valueRegistry->type($parameter->typeValue)
					])
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}