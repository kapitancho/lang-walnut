<?php

namespace Walnut\Lang\NativeCode\Record;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class With implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$parameterType = $this->toBaseType($parameterType);
		if ($targetType instanceof RecordType && $parameterType instanceof RecordType) {
			$recTypes = [];
			foreach($targetType->types as $tKey => $tType) {
				$pType = $parameterType->types[$tKey] ?? null;
				if ($tType instanceof OptionalKeyType) {
					$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
						$typeRegistry->optionalKey(
							$typeRegistry->union([
								$tType->valueType,
								$pType->valueType
							])
						) : $pType ?? $typeRegistry->optionalKey(
							$typeRegistry->union([
								$tType->valueType,
								$parameterType->restType
							])
						);
				} else {
					$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
						$typeRegistry->union([
							$tType,
							$pType->valueType
						]): $pType ?? $typeRegistry->union([
						$tType,
						$parameterType->restType
					]);
				}
			}
			foreach ($parameterType->types as $pKey => $pType) {
				$recTypes[$pKey] ??= $pType;
			}
			return $typeRegistry->record($recTypes,
				$typeRegistry->union([
					$targetType->restType,
					$parameterType->restType
				])
			);
		}
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof MapType) {
				return $typeRegistry->map(
					$typeRegistry->union([
						$targetType->itemType,
						$parameterType->itemType
					]),
					max($targetType->range->minLength, $parameterType->range->minLength),
					$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						((int)(string)$targetType->range->maxLength) + ((int)(string)$parameterType->range->maxLength)
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
		if ($target instanceof RecordValue) {
			if ($parameter instanceof RecordValue) {
				return $programRegistry->valueRegistry->record([
					... $target->values, ... $parameter->values
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