<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryIntegerDivide implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType) {
				if ((string)$parameterType->numberRange === '1') {
					return $targetType;
				}

				return $parameterType->contains(0) ?
					$typeRegistry->result(
						$typeRegistry->integer(),
						$typeRegistry->atom(new TypeNameIdentifier('NotANumber'))
					) : $typeRegistry->integer();
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
		$targetValue = $target;
		$parameterValue = $parameter;
		

		if ($targetValue instanceof IntegerValue) {
			if ($parameterValue instanceof IntegerValue) {
				if ((int)(string)$parameterValue->literalValue === 0) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					);
				}
                return $programRegistry->valueRegistry->integer(
	                intdiv((string)$targetValue->literalValue, (string)$parameterValue->literalValue)
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