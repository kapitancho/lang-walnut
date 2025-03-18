<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Ln implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
        if ($targetType instanceof RealType || $targetType instanceof RealSubsetType) {
			$min = $targetType->range->minValue;
			$max = $targetType->range->maxValue;
            $real = $programRegistry->typeRegistry->real(max: $max);
            return $min > 0 ? $real :
                $programRegistry->typeRegistry->result(
                    $real,
                    $programRegistry->typeRegistry->atom(
                        new TypeNameIdentifier('NotANumber')
                    )
                );
		}
		// @codeCoverageIgnoreStart
        throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;

		if ($targetValue instanceof RealValue || $targetValue instanceof IntegerValue) {
            $val = (string)$targetValue->literalValue;
			return ($val > 0 ? $programRegistry->valueRegistry->real(
				log((string)$val)
			) : $programRegistry->valueRegistry->error(
                $programRegistry->valueRegistry->atom(
                    new TypeNameIdentifier("NotANumber")
                )
            ));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}