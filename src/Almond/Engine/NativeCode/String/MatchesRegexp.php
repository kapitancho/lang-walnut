<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, SealedType, StringValue, SealedValue> */
final readonly class MatchesRegexp extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType instanceof SealedType && $parameterType->name->equals(
			CoreType::RegExp->typeName()
		) ? null : sprintf(
			"The parameter type %s is not a subtype of RegExp",
			$parameterType
		);
	}

	protected function getValidator(): callable {
		return fn(StringType $targetType, Type $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, SealedValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(
				(bool)@preg_match(
					$parameter->value->literalValue,
					$target->literalValue,
				)
			);
	}

	protected function isParameterValueValid(Value $parameter, callable $executor): bool {
		return $parameter instanceof SealedValue && $parameter->type->name->equals(
			CoreType::RegExp->typeName()
		);
	}

}
