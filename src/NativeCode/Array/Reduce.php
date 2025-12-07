<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Reduce implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			if ($parameterType->isSubtypeOf(
				$typeRegistry->record([
					'reducer' => $typeRegistry->function(
						$typeRegistry->record([
							'result' => $typeRegistry->nothing,
							'item' => $type->itemType,
						]),
						$typeRegistry->any
					),
					'initial' => $typeRegistry->any,
				])
			)) {
				$parameterType = $this->toBaseType($parameterType);
				$reducerType = $this->toBaseType($parameterType->types['reducer']);
				$initialType = $this->toBaseType($parameterType->types['initial']);

				$reducerParamType = $this->toBaseType($reducerType->parameterType);
				$resultType = $reducerParamType->types['result'];
				$itemType = $reducerParamType->types['item'];

				if (!$reducerType->returnType->isSubtypeOf($resultType)) {
					throw new AnalyserException(
						sprintf(
							"[%s] Reducer return type %s must match result type %s",
							__CLASS__,
							$reducerType->returnType,
							$resultType
						)
					);
				}
				if (!$initialType->isSubtypeOf($resultType)) {
					throw new AnalyserException(
						sprintf(
							"[%s] Initial value type %s must match result type %s",
							__CLASS__,
							$initialType,
							$resultType
						)
					);
				}
				return $resultType;
			}
			throw new AnalyserException(sprintf("[%s] Parameter must be a record with 'reducer' and 'initial' fields", __CLASS__));
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
			if ($parameter instanceof RecordValue) {
				if (isset($parameter->values['reducer']) && isset($parameter->values['initial'])) {
					$reducer = $parameter->values['reducer'];
					$accumulator = $parameter->values['initial'];

					if ($reducer instanceof FunctionValue) {
						foreach ($target->values as $item) {
							$reducerParam = $programRegistry->valueRegistry->record(['result' => $accumulator, 'item' => $item]);
							$accumulator = $reducer->execute($programRegistry->executionContext, $reducerParam);
						}
						return $accumulator;
					}
				}
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
