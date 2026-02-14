<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\ValueConverter;

/** @extends NativeMethod<Type, TypeType, Value, TypeValue> */
final readonly class Shape extends NativeMethod {

	protected function getValidator(): callable {
		$valueConverter = new ValueConverter(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext,
		);
		return fn(Type $targetType, TypeType $parameterType, mixed $origin): ValidationSuccess|ValidationFailure =>
			$valueConverter->analyseConvertValueToShape(
				$targetType,
				$parameterType->refType,
				$origin
			);
	}

	protected function getExecutor(): callable {
		$valueConverter = new ValueConverter(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext,
		);
		return fn(Value $target, TypeValue $parameter): Value =>
			$valueConverter->convertValueToShape(
				$target,
				$parameter->typeValue
			);
	}

}
