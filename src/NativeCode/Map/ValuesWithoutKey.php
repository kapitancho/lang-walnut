<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ValuesWithoutKey implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $this->context->typeRegistry->result(
					$this->context->typeRegistry->map(
						$targetType->itemType,
						$targetType->range->maxLength === PlusInfinity::value ?
							$targetType->range->minLength : max(0,
							min(
								$targetType->range->minLength - 1,
								$targetType->range->maxLength - 1
							)),
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : $targetType->range->maxLength - 1
					),
					$this->context->typeRegistry->sealed(
						new TypeNameIdentifier("MapItemNotFound")
					)
				);
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof StringValue) {
				$values = $targetValue->values;
				if (!isset($values[$parameterValue->literalValue])) {
					return TypedValue::forValue($this->context->valueRegistry->error(
						$this->context->valueRegistry->sealedValue(
							new TypeNameIdentifier('MapItemNotFound'),
							$this->context->valueRegistry->record(['key' => $parameterValue])
						)
					));
				}
				unset($values[$parameterValue->literalValue]);
				return TypedValue::forValue($this->context->valueRegistry->record($values));
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