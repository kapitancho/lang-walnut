<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Sqrt implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			$real = $typeRegistry->real(0);
			$minValue = $targetType->numberRange->min;
			return $minValue === MinusInfinity::value || $minValue->value < 0 ?
				$typeRegistry->result(
					$real,
					$typeRegistry->atom(
						new TypeNameIdentifier('NotANumber')
					)
				) :
				$real;
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

		if ($target instanceof IntegerValue || $target instanceof RealValue) {
			$val = (string)$target->literalValue;
			return $val >= 0 ?
				$programRegistry->valueRegistry->real($target->literalValue->sqrt()) :
				$programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->atom(
		                new TypeNameIdentifier("NotANumber")
                    )
				);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}