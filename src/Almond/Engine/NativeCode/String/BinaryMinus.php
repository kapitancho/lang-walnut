<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, Type, StringValue, Value> */
final readonly class BinaryMinus extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool|Type {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->string(1),
				$this->typeRegistry->array(
					$this->typeRegistry->string(1)
				),
				$this->typeRegistry->set(
					$this->typeRegistry->string(1)
				)
			])
		);
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, Type $parameterType): StringType {
			return $this->typeRegistry->string(
				0,
				$targetType->range->maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, Value $parameter): StringValue {
			$values = [];
			if ($parameter instanceof StringValue) {
				$values[] = $parameter->literalValue;
			} elseif ($parameter instanceof TupleValue || $parameter instanceof SetValue) {
				foreach ($parameter->values as $item) {
					if (!($item instanceof StringValue)) {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid parameter type");
						// @codeCoverageIgnoreEnd
					}
					$values[] = $item->literalValue;
				}
			}
			return $this->valueRegistry->string(
				str_replace($values, '', $target->literalValue)
			);
		};
	}
}
