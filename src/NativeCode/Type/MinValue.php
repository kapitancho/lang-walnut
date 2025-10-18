<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MinValue implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				return $programRegistry->typeRegistry->union([
					$programRegistry->typeRegistry->integer(),
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('MinusInfinity'))
				]);
			}
			if ($refType instanceof RealType) {
				return $programRegistry->typeRegistry->union([
					$programRegistry->typeRegistry->real(),
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('MinusInfinity'))
				]);
			}
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

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType) {
				return ($typeValue->numberRange->min === MinusInfinity::value ?
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('MinusInfinity')) :
					$programRegistry->valueRegistry->integer($typeValue->numberRange->min->value));
			}
			if ($typeValue instanceof RealType) {
				return ($typeValue->numberRange->min === MinusInfinity::value ?
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('MinusInfinity')) :
					$programRegistry->valueRegistry->real($typeValue->numberRange->min->value));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}