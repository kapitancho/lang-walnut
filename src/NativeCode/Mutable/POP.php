<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class POP implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
            $valueType = $this->toBaseType($t->valueType);
		    if ($valueType instanceof ArrayType && (int)(string)$valueType->range->minLength === 0) {
                return $typeRegistry->result(
                    $valueType->itemType,
                    $typeRegistry->core->itemNotFound
                );
            }
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
		$v = $target;
		if ($v instanceof MutableValue) {
            $targetType = $this->toBaseType($v->targetType);
			if ($targetType instanceof ArrayType) {
				$values = $v->value->values;
				if (count($values) > 0) {
					$value = array_pop($values);
					$v->value = $programRegistry->valueRegistry->tuple($values);

					return $value;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->core->itemNotFound
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}