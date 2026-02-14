<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<StringType|ArrayType, NullType, NullValue> */
final readonly class REVERSE extends MutableNativeMethod {

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return $targetValueType instanceof StringType || $targetValueType instanceof ArrayType;
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, NullType $parameterType): MutableType =>
			$targetType;
	}

	private function reverse(string $str): string {
		preg_match_all('/./us', $str, $matches);
		return implode('', array_reverse($matches[0]));
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, NullValue $parameter): MutableValue {
			$t = $target->value;
			if ($t instanceof TupleValue) {
				$target->value = $this->valueRegistry->tuple(array_reverse($t->values));
				return $target;
			}
			if ($t instanceof StringValue) {
				$target->value = $this->valueRegistry->string($this->reverse($t->literalValue));
				return $target;
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		};
	}

}
