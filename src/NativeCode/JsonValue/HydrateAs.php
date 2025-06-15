<?php

namespace Walnut\Lang\NativeCode\JsonValue;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;
use Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

final readonly class HydrateAs implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($parameterType instanceof TypeType) {
			return $programRegistry->typeRegistry->result(
				$parameterType->refType,
				$programRegistry->typeRegistry->withName(new TypeNameIdentifier("HydrationError"))
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
			try {
				return new Hydrator(
					$programRegistry,
				)->hydrate($targetValue, $parameterValue->typeValue, 'value');
			} catch (HydrationException $e) {
				return ($programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier("HydrationError"),
						$programRegistry->valueRegistry->record([
							'value' => $e->value,
							'hydrationPath' => $programRegistry->valueRegistry->string($e->hydrationPath),
							'errorMessage' => $programRegistry->valueRegistry->string($e->errorMessage),
						])
					)
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}