<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Zip implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof RecordType && $pType instanceof RecordType) {
			$resultType = [];
			$keys = array_values(array_unique(array_merge(
				array_keys($type->types), array_keys($pType->types)
			)));
			foreach ($keys as $key) {
				$tg = $type->types[$key] ?? $type->restType;
				$pr = $pType->types[$key] ?? $pType->restType;
				$isOptional = false;
				if ($tg instanceof OptionalKeyType) {
					$tg = $tg->valueType;
					$isOptional = true;
				}
				if ($pr instanceof OptionalKeyType) {
					$pr = $pr->valueType;
					$isOptional = true;
				}
				if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
					$tuple = $typeRegistry->tuple([$tg, $pr]);
					$resultType[$key] = $isOptional ? $typeRegistry->optionalKey($tuple) : $tuple;
				}
			}
			return $typeRegistry->record($resultType,
				$type->restType instanceof NothingType || $pType->restType instanceof NothingType ?
					$typeRegistry->nothing : $typeRegistry->tuple([
					$type->restType,
					$pType->restType,
				])
			);
		}
		$type = $type instanceof RecordType ? $type->asMapType() : $type;
		$pType = $pType instanceof RecordType ? $pType->asMapType() : $pType;
		if ($type instanceof MapType) {
			if ($pType instanceof MapType) {
				return $typeRegistry->map(
					$typeRegistry->tuple([
						$type->itemType,
						$pType->itemType,
					]),
					min( $type->range->minLength, $pType->range->minLength),
					match(true) {
						$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
						$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
						default => min($type->range->maxLength, $pType->range->maxLength)
					},
					$typeRegistry->intersection([
						$type->keyType,
						$pType->keyType
					])
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
		        $values = $target->values;
		        $pValues = $parameter->values;
		        $result = [];
		        foreach($values as $key => $value) {
			        $pValue = $pValues[$key] ?? null;
			        if (!$pValue) {
				        continue;
			        }
			        $result[$key] = $programRegistry->valueRegistry->tuple([$value, $pValue]);
		        }
		        return $programRegistry->valueRegistry->record($result);
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