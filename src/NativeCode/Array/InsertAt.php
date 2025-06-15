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
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class InsertAt implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($targetType instanceof ArrayType) {
			$pInt = $programRegistry->typeRegistry->integer(0);
			$pType = $programRegistry->typeRegistry->record([
				"value" => $programRegistry->typeRegistry->any,
				"index" => $pInt
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				$returnType = $programRegistry->typeRegistry->array(
					$programRegistry->typeRegistry->union([
						$targetType->itemType,
						$parameterType->types['value']
					]),
					$targetType->range->minLength + 1,
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + 1
				);
				return
					$parameterType->types['index']->range->maxValue >= 0 &&
					$parameterType->types['index']->range->maxValue <= $targetType->range->minLength ?
					$returnType : $programRegistry->typeRegistry->result($returnType,
						$programRegistry->typeRegistry->data(new TypeNameIdentifier('IndexOutOfRange'))
					);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		$parameterValue = $parameter;

		if ($targetValue instanceof TupleValue) {
			if ($parameterValue instanceof RecordValue) {
				$value = $parameterValue->valueOf('value');
				$index = $parameterValue->valueOf('index');
				if ($index instanceof IntegerValue) {
					$idx = (string)$index->literalValue;
					$values = $targetValue->values;
					if ($idx >= 0 && $idx <= count($values)) {
						array_splice(
							$values,
							$idx,
							0,
							[$value]
						);
						return ($programRegistry->valueRegistry->tuple($values));
					}
					return ($programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->dataValue(
							new TypeNameIdentifier('IndexOutOfRange'),
							$programRegistry->valueRegistry->record([
								'index' => $index
							])
						)
					));
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd
			}
			$values = $targetValue->values;
			$values[] = $parameterValue;
			return ($programRegistry->valueRegistry->tuple($values));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}