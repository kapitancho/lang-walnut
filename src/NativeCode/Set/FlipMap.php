<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FlipMap implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
        $targetType = $this->toBaseType($targetType);
        $type = $targetType;
		if ($type instanceof SetType) {
			$itemType = $type->itemType;
			if ($itemType->isSubtypeOf($programRegistry->typeRegistry->string())) {
                $parameterType = $this->toBaseType($parameterType);
                if ($parameterType instanceof FunctionType) {
                    if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
                        $r = $parameterType->returnType;
                        $errorType = $r instanceof ResultType ? $r->errorType : null;
                        $returnType = $r instanceof ResultType ? $r->returnType : $r;
                        $t = $programRegistry->typeRegistry->map(
                            $returnType,
                            min(1, $type->range->minLength),
                            $type->range->maxLength,
                        );
                        return $errorType ? $programRegistry->typeRegistry->result($t, $errorType) : $t;
                    }
                    throw new AnalyserException(
						sprintf(
                            "The parameter type %s of the callback function is not a subtype of %s",
                            $type->itemType,
                            $parameterType->parameterType
						)
                    );
                }
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
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
        $targetValue = $this->toBaseValue($targetValue);
        if ($targetValue instanceof SetValue && $parameterValue instanceof FunctionValue) {
            $values = $targetValue->values;
            $result = [];
            foreach($values as $value) {
                if (!($value instanceof StringValue)) {
                    // @codeCoverageIgnoreStart
                    throw new ExecutionException("Invalid target value");
                    // @codeCoverageIgnoreEnd
                }
                $r = $parameterValue->execute($programRegistry->executionContext, $value);
	            if ($r instanceof ErrorValue) {
                    return TypedValue::forValue($r);
                }
                $result[$value->literalValue] = $r;
            }
            return TypedValue::forValue($programRegistry->valueRegistry->record($result));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}