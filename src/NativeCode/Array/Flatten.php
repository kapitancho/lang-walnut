<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Flatten implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
            $itemType = $type->itemType;
            if ($itemType instanceof ArrayType) {
                return $this->context->typeRegistry->array(
                    $itemType->itemType,
                    $type->range->minLength * $itemType->range->minLength,
                    $type->range->maxLength === PlusInfinity::value ||
                        $itemType->range->maxLength === PlusInfinity::value ?
                        PlusInfinity::value :
                        $type->range->maxLength * $itemType->range->maxLength,
                );
            }
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;
            $result = [];
            foreach($values as $value) {
                if ($value instanceof TupleValue) {
                    $result = array_merge($result, $value->values);
                } else {
                    // @codeCoverageIgnoreStart
                    throw new ExecutionException("Invalid target value");
                    // @codeCoverageIgnoreEnd
                }
            }
			return TypedValue::forValue($this->context->valueRegistry->tuple($result));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}