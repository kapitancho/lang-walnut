<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType || $parameterType instanceof RealType) {
				$includesZero = $parameterType->contains(0);
				$returnType = $parameterType instanceof IntegerType ?
					$typeRegistry->integer() : $typeRegistry->real();

				return $includesZero ? $typeRegistry->result(
					$returnType,
					$typeRegistry->core->notANumber
				) : $returnType;
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof IntegerValue) {
			if ($parameter instanceof IntegerValue) {
				if ((int)(string)$parameter->literalValue === 0) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->core->notANumber
					);
				}
                return $programRegistry->valueRegistry->integer(
	                $target->literalValue % $parameter->literalValue
                );
			}
			if ($parameter instanceof RealValue) {
				if ((float)(string)$parameter->literalValue === 0.0) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->core->notANumber
					);
				}
                return $programRegistry->valueRegistry->real(
	                fmod((float)(string)$target->literalValue, (float)(string)$parameter->literalValue)
                );
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}