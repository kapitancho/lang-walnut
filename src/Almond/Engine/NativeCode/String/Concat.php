<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, Type, StringValue, Value> */
final readonly class Concat extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool|Type {
		return $parameterType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, Type $parameterType): StringType {
			if ($parameterType instanceof StringType) {
				return $this->typeRegistry->string(
					$targetType->range->minLength + $parameterType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxLength + $parameterType->range->maxLength
				);
			}
			return $this->typeRegistry->string();
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, Value $parameter): StringValue {
			if ($parameter instanceof StringValue) {
				return $this->valueRegistry->string($target->literalValue . $parameter->literalValue);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		};
	}
}
