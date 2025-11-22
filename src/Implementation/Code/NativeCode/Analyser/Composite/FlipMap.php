<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait FlipMap {
	use BaseType;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		ArrayType|SetType $targetType,
		Type $parameterType,
		bool $unique
	): MapType|ResultType {
		$itemType = $targetType->itemType;
		if ($itemType->isSubtypeOf($typeRegistry->string())) {
            $parameterType = $this->toBaseType($parameterType);
            if ($parameterType instanceof FunctionType) {
                if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
                    $r = $parameterType->returnType;
                    $errorType = $r instanceof ResultType ? $r->errorType : null;
                    $returnType = $r instanceof ResultType ? $r->returnType : $r;
                    $t = $typeRegistry->map(
                        $returnType,
	                    $unique ? $targetType->range->minLength : min(1, $targetType->range->minLength),
                        $targetType->range->maxLength,
	                    $itemType
                    );
                    return $errorType ? $typeRegistry->result($t, $errorType) : $t;
                }
                throw new AnalyserException(
					sprintf(
                        "The parameter type %s of the callback function is not a subtype of %s",
                        $targetType->itemType,
                        $parameterType->parameterType
					)
                );
            }
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		TupleValue|SetValue $target,
		Value $parameter
	): ErrorValue|RecordValue {
		if ($parameter instanceof FunctionValue) {
			$values = $target->values;
			$result = [];
			foreach($values as $value) {
				if (!($value instanceof StringValue)) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$r = $parameter->execute($programRegistry->executionContext, $value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				$result[$value->literalValue] = $r;
			}
			return $programRegistry->valueRegistry->record($result);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter type");
		// @codeCoverageIgnoreEnd
	}

}