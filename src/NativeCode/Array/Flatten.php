<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Flatten implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
            $itemType = $type->itemType;
            if ($itemType->isSubtypeOf($programRegistry->typeRegistry->array())) {
				if ($itemType instanceof ArrayType) {
	                return $programRegistry->typeRegistry->array(
	                    $itemType->itemType,
		                ((int)(string)$type->range->minLength) * ((int)(string)$itemType->range->minLength),
	                    $type->range->maxLength === PlusInfinity::value ||
	                        $itemType->range->maxLength === PlusInfinity::value ?
	                        PlusInfinity::value :
		                    ((int)(string)$type->range->maxLength) * ((int)(string)$itemType->range->maxLength),
	                );
				}
				return $programRegistry->typeRegistry->array();
            }
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
			return ($programRegistry->valueRegistry->tuple($result));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}