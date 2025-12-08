<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Zip implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof TupleType && $pType instanceof TupleType) {
			$resultType = [];
			$maxLength = max(count($type->types), count($pType->types));
			for ($i = 0; $i < $maxLength; $i++) {
				$tg = $type->types[$i] ?? $type->restType;
				$pr = $pType->types[$i] ?? $pType->restType;
				if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
					$resultType[] = $typeRegistry->tuple([$tg, $pr]);
				} else {
					break;
				}
			}
			return $typeRegistry->tuple($resultType,
				$type->restType instanceof NothingType || $pType->restType instanceof NothingType ?
					$typeRegistry->nothing : $typeRegistry->tuple([
						$type->restType,
						$pType->restType,
				])
			);
		}
		$type = $type instanceof TupleType ? $type->asArrayType() : $type;
		$pType = $pType instanceof TupleType ? $pType->asArrayType() : $pType;
		if ($type instanceof ArrayType) {
			if ($pType instanceof ArrayType) {
				return $typeRegistry->array(
					$typeRegistry->tuple([
						$type->itemType,
						$pType->itemType,
					]),
					min( $type->range->minLength, $pType->range->minLength),
					match(true) {
						$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
						$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
						default => min($type->range->maxLength, $pType->range->maxLength)
					}
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
	        if ($parameter instanceof TupleValue) {
		        $values = $target->values;
		        $pValues = $parameter->values;
		        $result = [];
		        foreach($values as $index => $value) {
			        $pValue = $pValues[$index] ?? null;
			        if (!$pValue) {
				        break;
			        }
			        $result[] = $programRegistry->valueRegistry->tuple([$value, $pValue]);
		        }
		        return $programRegistry->valueRegistry->tuple($result);
	        }
	        // @codeCoverageIgnoreStart
	        throw new ExecutionException("Invalid parameter type");
	        // @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}