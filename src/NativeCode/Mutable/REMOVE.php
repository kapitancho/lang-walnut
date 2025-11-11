<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class REMOVE implements NativeMethod {
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
		    if ($valueType instanceof SetType && (int)(string)$valueType->range->minLength === 0) {
                return $typeRegistry->result(
                    $valueType->itemType,
                    $typeRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
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
			if ($targetType instanceof SetType && $v->value instanceof SetValue) {
				$k = (string)$parameter;
				$vs = $v->value->valueSet;
				if (array_key_exists($k, $vs)) {
					unset($vs[$k]);
					$v->value = $programRegistry->valueRegistry->set($vs);
					return $parameter;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}