<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Take implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof TupleType && $pType instanceof IntegerSubsetType && count($pType->subsetValues) === 1) {
			$param = (int)(string)$pType->subsetValues[0];
			if ($param >= 0) {
				return $typeRegistry->tuple(
					array_slice($type->types, 0, $param),
					$param > count($type->types) ? $type->restType : $typeRegistry->nothing
				);
			}
		}

		$type = $type instanceof TupleType ? $type->asArrayType() : $type;
		if ($type instanceof ArrayType) {
			/** @var IntegerType $parameterType */
			if ($pType->isSubtypeOf($typeRegistry->integer(0))) {
				$maxLength = match(true) {
					$pType->numberRange->max === PlusInfinity::value => $type->range->maxLength,
					$type->range->maxLength === PlusInfinity::value,
					$type->range->maxLength > $pType->numberRange->max->value => $pType->numberRange->max->value,
					default => $type->range->maxLength,
				};
				return $typeRegistry->array(
					$type->itemType,
					min($pType->numberRange->min->value, $type->range->minLength),
					$maxLength
				);
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
		if ($target instanceof TupleValue) {
			if ($parameter instanceof IntegerValue && $parameter->literalValue >= 0) {
				return $programRegistry->valueRegistry->tuple(
					array_slice($target->values, 0, (int)(string)$parameter->literalValue)
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
