<?php

namespace Walnut\Lang\NativeCode\JsonValue;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\HydrationException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Hydrator\HydratorFactory;

final readonly class HydrateAs implements NativeMethod {

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($parameterType instanceof TypeType) {
			return $typeRegistry->result(
				$parameterType->refType,
				$typeRegistry->core->hydrationError
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
			try {
				return new HydratorFactory(
					$programRegistry->typeRegistry,
					$programRegistry->valueRegistry,
					$programRegistry->methodContext,
				)->hydrator->hydrate($target, $parameter->typeValue, 'value');
			} catch (HydrationException $e) {
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->core->hydrationError(
						$programRegistry->valueRegistry->record([
							'value' => $e->value,
							'hydrationPath' => $programRegistry->valueRegistry->string($e->hydrationPath),
							'errorMessage' => $programRegistry->valueRegistry->string($e->errorMessage),
						])
					)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}