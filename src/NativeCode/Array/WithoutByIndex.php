<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithoutByIndex implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof TupleType && $pType instanceof IntegerSubsetType && count($pType->subsetValues) === 1) {
			$param = (int)(string)$pType->subsetValues[0];
			if ($param >= 0) {
				$hasError = $param >= count($type->types);
				$paramType = $type->types[$param] ?? $type->restType;
				$paramItemTypes = $type->types;
				array_splice($paramItemTypes, $param, 1);
				$returnType = $paramType instanceof NothingType ?
					$paramType :
					$typeRegistry->record([
						'element' => $paramType,
						'array' => $hasError ? $type : $typeRegistry->tuple(
							$paramItemTypes,
							$type->restType
						)
					]);
				return $hasError ?
					$typeRegistry->result(
						$returnType,
						$typeRegistry->data(
							new TypeNameIdentifier("IndexOutOfRange")
						)
					) :
					$returnType;
			}
		}
		$type = $type instanceof TupleType ? $type->asArrayType() : $type;
		if ($type instanceof ArrayType) {
			if ($pType instanceof IntegerType) {
				$returnType = $typeRegistry->record([
					'element' => $type->itemType,
					'array' => $typeRegistry->array(
						$type->itemType,
						max(0, $type->range->minLength - 1),
						$type->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($type->range->maxLength - 1, 0)
					)
				]);
				return
					$pType->numberRange->min instanceof NumberIntervalEndpoint &&
					($pType->numberRange->min->value + ($pType->numberRange->min->inclusive ? 0 : 1)) >= 0 &&
					$pType->numberRange->max instanceof NumberIntervalEndpoint &&
					($pType->numberRange->max->value - ($pType->numberRange->max->inclusive ? 0 : 1)) <
						$type->range->maxLength ?
						$returnType :
						$typeRegistry->result(
							$returnType,
							$typeRegistry->data(
								new TypeNameIdentifier("IndexOutOfRange")
							)
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
			if ($parameter instanceof IntegerValue) {
				$values = $target->values;
				$p = (string)$parameter->literalValue;
				if (!array_key_exists($p, $values)) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->dataValue(
							new TypeNameIdentifier('IndexOutOfRange'),
							$programRegistry->valueRegistry->record(['index' => $parameter])
						)
					);
				}
				$removed = array_splice($values, (int)$p, 1);
				return $programRegistry->valueRegistry->record([
					'element' => $removed[0],
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