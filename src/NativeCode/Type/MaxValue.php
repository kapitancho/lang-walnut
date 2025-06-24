<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
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

final readonly class MaxValue implements NativeMethod {

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
					$programRegistry->typeRegistry->integerFull(),
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('PlusInfinity'))
				]);
			}
			if ($refType instanceof RealType) {
				return $programRegistry->typeRegistry->union([
					$programRegistry->typeRegistry->real(),
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('PlusInfinity'))
				]);
			}
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

		if ($targetValue instanceof TypeValue) {
			//TODO: yyy - open vs closed intervals
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType) {
				return ($typeValue->numberRange->max === PlusInfinity::value ?
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('PlusInfinity')) :
					$programRegistry->valueRegistry->integer($typeValue->numberRange->max->value)
				);
			}
			if ($typeValue instanceof RealType) {
				return ($typeValue->numberRange->max === PlusInfinity::value ?
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('PlusInfinity')) :
					$programRegistry->valueRegistry->real($typeValue->numberRange->max->value)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}