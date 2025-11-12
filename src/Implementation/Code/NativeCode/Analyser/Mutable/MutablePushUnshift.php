<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait MutablePushUnshift {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
            $valueType = $this->toBaseType($t->valueType);
		    if ($valueType instanceof ArrayType && $valueType->range->maxLength === PlusInfinity::value) {
			    $p = $this->toBaseType($parameterType);
				if ($p->isSubtypeOf($valueType->itemType)) {
					return $t;
				}
			    // @codeCoverageIgnoreStart
	            throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	            // @codeCoverageIgnoreEnd
            }
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param callable(list<Value>, Value): list<Value> $insertFn
	 */
	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		callable $insertFn
	): Value {
		$v = $target;
		if ($v instanceof MutableValue) {
            $targetType = $this->toBaseType($v->targetType);
			$mv = $v->value;
			if ($targetType instanceof ArrayType && $mv instanceof TupleValue) {
				if ($parameter->type->isSubtypeOf($targetType->itemType)) {
					$arr = $mv->values;
					$arr = $insertFn($arr, $parameter);
					$v->value = $programRegistry->valueRegistry->tuple($arr);
					return $target;
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd

			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}