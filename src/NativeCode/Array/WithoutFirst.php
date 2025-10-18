<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithoutFirst implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$returnType = $programRegistry->typeRegistry->record([
				'element' => $type->itemType,
				'array' => $programRegistry->typeRegistry->array(
					$type->itemType,
					max(0, $type->range->minLength - 1),
					$type->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($type->range->maxLength - 1, 0)
				)
			]);
			return $type->range->minLength > 0 ? $returnType :
				$programRegistry->typeRegistry->result($returnType,
					$programRegistry->typeRegistry->atom(
						new TypeNameIdentifier("ItemNotFound")
					)
				);
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

		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;
			if (count($values) === 0) {
				return (
					$programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(
							new TypeNameIdentifier("ItemNotFound")
						)
					)
				);
			}
			$element = array_shift($values);
			return ($programRegistry->valueRegistry->record([
				'element' => $element,
				'array' => $programRegistry->valueRegistry->tuple($values)
			]));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}