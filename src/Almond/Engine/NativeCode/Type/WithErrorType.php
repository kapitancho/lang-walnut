<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<ResultType, TypeType, TypeValue> */
final readonly class WithErrorType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof ResultType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var ResultType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->type(
				$this->typeRegistry->result(
					$refType->returnType,
					$parameterType->refType
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var ResultType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			$result = $this->typeRegistry->result(
				$typeValue->returnType,
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
