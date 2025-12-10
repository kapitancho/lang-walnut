<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ArrayWithoutFirstIWithoutLast {
	use BaseType;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($targetType instanceof ArrayType) {
			if ($parameterType instanceof NullType) {
				$returnType = $typeRegistry->record([
					'element' => $targetType->itemType,
					'array' => $typeRegistry->array(
						$targetType->itemType,
						max(0, $targetType->range->minLength - 1),
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($targetType->range->maxLength - 1, 0)
					)
				]);
				return $targetType->range->minLength > 0 ? $returnType :
					$typeRegistry->result($returnType,
						$typeRegistry->atom(
							new TypeNameIdentifier("ItemNotFound")
						)
					);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param callable(non-empty-list<Value>): array{Value, list<Value>} $removeFn
	 */
	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		callable $removeFn
	): Value {
		if ($target instanceof TupleValue) {
			if ($parameter instanceof NullValue) {
				$values = $target->values;
				if (count($values) === 0) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(
							new TypeNameIdentifier("ItemNotFound")
						)
					);
				}
				[$element, $values] = $removeFn($values);
				return $programRegistry->valueRegistry->record([
					'element' => $element,
					'array' => $programRegistry->valueRegistry->tuple($values)
				]);
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