<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<\Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type, TypeType, TypeValue> */
final readonly class BinaryBitwiseAnd extends TypeNativeMethod {

	protected function getValidator(): callable {
		return fn(TypeType $targetType, TypeType $parameterType): TypeType =>
			$this->typeRegistry->type(
				$this->typeRegistry->intersection([
					$targetType->refType,
					$parameterType->refType
				])
			);
	}

	protected function getExecutor(): callable {
		return fn(TypeValue $target, TypeValue $parameter): TypeValue =>
			$this->valueRegistry->type(
				$this->typeRegistry->intersection([
					$target->typeValue,
					$parameter->typeValue
				])
			);
	}

}
