<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Filter implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$recordReturnType = null;
		if ($targetType instanceof RecordType) {
			$recordReturnType = $typeRegistry->record(
				array_map(
					fn(Type $type): OptionalKeyType =>
						$type instanceof OptionalKeyType ?
							$type :
							$typeRegistry->optionalKey($type),
					$targetType->types
				),
				$targetType->restType
			);
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf(
				$typeRegistry->result($typeRegistry->boolean, $typeRegistry->any)
			)) {
				$pType = $this->toBaseType($parameterType->returnType);
				if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
					$returnType = $recordReturnType ?? $typeRegistry->map(
						$targetType->itemType,
						0,
						$targetType->range->maxLength,
						$targetType->keyType
					);
					return $pType instanceof ResultType ? $typeRegistry->result(
						$returnType,
						$pType->errorType
					) : $returnType;
				}
				throw new AnalyserException(
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$targetType->itemType,
						$parameterType->parameterType
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
		if ($target instanceof RecordValue && $parameter instanceof FunctionValue) {
			$values = $target->values;
			$result = [];
			$true = $programRegistry->valueRegistry->true;
			foreach($values as $key => $value) {
				$r = $parameter->execute($programRegistry->executionContext, $value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($true->equals($r)) {
					$result[$key] = $value;
				}
			}
			return $programRegistry->valueRegistry->record($result);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}