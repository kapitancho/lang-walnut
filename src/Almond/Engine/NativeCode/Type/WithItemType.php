<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<ArrayType|MapType|SetType, TypeType, TypeValue> */
final readonly class WithItemType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof ArrayType || $refType instanceof MapType || $refType instanceof SetType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var ArrayType|MapType|SetType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof ArrayType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->array(
						$parameterType->refType,
						$refType->range->minLength,
						$refType->range->maxLength)
				);
			}
			if ($refType instanceof MapType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->map(
						$parameterType->refType,
						$refType->range->minLength,
						$refType->range->maxLength,
						$refType->keyType
					)
				);
			}
			return $this->typeRegistry->type(
				$this->typeRegistry->set(
					$parameterType->refType,
					$refType->range->minLength,
					$refType->range->maxLength)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var ArrayType|MapType|SetType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof ArrayType) {
				$result = $this->typeRegistry->array(
					$parameter->typeValue,
					$typeValue->range->minLength,
					$typeValue->range->maxLength,
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof MapType) {
				$result = $this->typeRegistry->map(
					$parameter->typeValue,
					$typeValue->range->minLength,
					$typeValue->range->maxLength,
					$typeValue->keyType
				);
				return $this->valueRegistry->type($result);
			}
			/** @var SetType $typeValue */
			$result = $this->typeRegistry->set(
				$parameter->typeValue,
				$typeValue->range->minLength,
				$typeValue->range->maxLength,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
