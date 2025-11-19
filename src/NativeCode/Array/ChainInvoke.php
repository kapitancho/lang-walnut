<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ChainInvoke implements NativeMethod {
	use BaseType;

    public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
        Type      $targetType,
        Type      $parameterType
    ): Type {
        $targetType = $this->toBaseType($targetType);
        $type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
        if ($type instanceof ArrayType) {
            $itemType = $this->toBaseType($type->itemType);
            if ($itemType instanceof NothingType) {
				return $parameterType;
            }
            if ($itemType instanceof FunctionType) {
                if ($itemType->returnType->isSubtypeOf($itemType->parameterType)) {
                    if ($parameterType->isSubtypeOf($itemType->parameterType)) {
                        return $itemType->returnType;
                    }
                    throw new AnalyserException(
						sprintf(
                            "The parameter type %s is not a subtype of %s",
                            $parameterType,
                            $itemType->parameterType
						)
                    );
                }
            }
        }
        throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
    }

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
        if ($target instanceof TupleValue) {
            foreach($target->values as $fnValue) {
				if ($fnValue instanceof FunctionValue) {
                    $parameter = $fnValue->execute(
						$programRegistry->executionContext,
						$parameter
                    );
	            }
            }
        }
		return $parameter;
    }

}