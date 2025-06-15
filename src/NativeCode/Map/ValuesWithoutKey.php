<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ValuesWithoutKey implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $programRegistry->typeRegistry->result(
					$programRegistry->typeRegistry->map(
						$targetType->itemType,
						$targetType->range->maxLength === PlusInfinity::value ?
							$targetType->range->minLength : max(0,
							min(
								$targetType->range->minLength - 1,
								$targetType->range->maxLength - 1
							)),
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($targetType->range->maxLength - 1, 0)
					),
					$programRegistry->typeRegistry->data(
						new TypeNameIdentifier("MapItemNotFound")
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
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;

		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof StringValue) {
				$values = $targetValue->values;
				if (!isset($values[$parameterValue->literalValue])) {
					return ($programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->dataValue(
							new TypeNameIdentifier('MapItemNotFound'),
							$programRegistry->valueRegistry->record(['key' => $parameterValue])
						)
					));
				}
				unset($values[$parameterValue->literalValue]);
				return ($programRegistry->valueRegistry->record($values));
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