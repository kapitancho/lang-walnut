<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<MapType, TypeType, TypeValue> */
final readonly class WithKeyType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType): bool {
		return $this->toBaseType($targetRefType) instanceof MapType;
	}

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		/** @var TypeType $parameterType */
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->type($this->typeRegistry->string())
		);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var MapType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->type(
				$this->typeRegistry->map(
					$refType->itemType,
					$refType->range->minLength,
					$refType->range->maxLength,
					$parameterType->refType,
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var MapType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			$result = $this->typeRegistry->map(
				$typeValue->itemType,
				$typeValue->range->minLength,
				$typeValue->range->maxLength,
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
