<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FILTER implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof MutableType) {
			$type = $this->toBaseType($targetType->valueType);
			if ($type->isSubtypeOf(
				$typeRegistry->union([
					$typeRegistry->array($type->itemType),
					$typeRegistry->map($type->itemType),
					$typeRegistry->set($type->itemType),
				])
			) &&
				!$type->isSubtypeOf($typeRegistry->array($typeRegistry->any, 1)) &&
				!$type->isSubtypeOf($typeRegistry->map($typeRegistry->any, 1)) &&
				!$type->isSubtypeOf($typeRegistry->set($typeRegistry->any, 1))
			) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($typeRegistry->boolean)) {
					if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
						return $targetType;
					}
					throw new AnalyserException(
						sprintf(
							"The parameter type %s of the callback function is not a subtype of %s",
							$type->itemType,
							$parameterType->parameterType
						)
					);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		if ($target instanceof MutableValue && $parameter instanceof FunctionValue) {
			$v = $target->value;
			if ($v instanceof TupleValue || $v instanceof RecordValue || $v instanceof SetValue) {
				$values = $v->values;
				$result = [];
				$true = $programRegistry->valueRegistry->true;
				foreach($values as $key => $value) {
					$r = $parameter->execute($programRegistry->executionContext, $value);
					if ($true->equals($r)) {
						$result[$key] = $value;
					}
				}
				if (!$v instanceof RecordValue) {
					$result = array_values($result);
				}
				$output = match(true) {
					$v instanceof TupleValue => $programRegistry->valueRegistry->tuple($result),
					$v instanceof RecordValue => $programRegistry->valueRegistry->record($result),
					$v instanceof SetValue => $programRegistry->valueRegistry->set($result),
				};
				$target->value = $output;
				return $target;
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}