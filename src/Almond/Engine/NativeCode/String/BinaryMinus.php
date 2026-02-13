<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, Type, StringValue, Value> */
final readonly class BinaryMinus extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, Type $parameterType, mixed $origin): StringType|ValidationFailure {
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->union([
					$this->typeRegistry->string(1),
					$this->typeRegistry->array(
						$this->typeRegistry->string(1)
					),
					$this->typeRegistry->set(
						$this->typeRegistry->string(1)
					)
				])
			)) {
				return $this->typeRegistry->string(
					0,
					$targetType->range->maxLength
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
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
