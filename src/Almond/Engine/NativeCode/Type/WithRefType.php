<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<TypeType|ShapeType, TypeType, TypeValue> */
final readonly class WithRefType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof TypeType || $refType instanceof ShapeType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var TypeType|ShapeType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TypeType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->type(
						$parameterType->refType
					)
				);
			}
			return $this->typeRegistry->type(
				$this->typeRegistry->shape(
					$parameterType->refType
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var TypeType|ShapeType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof TypeType) {
				$result = $this->typeRegistry->type(
					$parameter->typeValue,
				);
				return $this->valueRegistry->type($result);
			}
			$result = $this->typeRegistry->shape(
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
