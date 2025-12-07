<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MAP implements NativeMethod {
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
			) && !$type->isSubtypeOf($typeRegistry->set($typeRegistry->any, 2))) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType) {
					if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
						if ($parameterType->returnType->isSubtypeOf($type->itemType)) {
							return $targetType;
						}
						throw new AnalyserException(
							sprintf(
								"The value type %s is not a subtype of the return type %s of the callback function",
								$parameterType->returnType,
								$type->itemType,
							)
						);
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
					$result[$key] = $r;
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