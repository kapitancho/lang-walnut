<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<FunctionType|MetaType, TypeType, TypeValue> */
final readonly class WithParameterType extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return $targetRefType instanceof FunctionType ||
			($targetRefType instanceof MetaType && $targetRefType->value === MetaTypeValue::Function) ?
			null : sprintf("Target ref type must be a Function type, got: %s", $targetRefType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var FunctionType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof FunctionType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->function(
						$parameterType->refType,
						$refType->returnType,
					)
				);
			}
			return $this->typeRegistry->type(
				$this->typeRegistry->function(
					$parameterType->refType,
					$this->typeRegistry->any
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var FunctionType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			$result = $this->typeRegistry->function(
				$parameter->typeValue,
				$typeValue->returnType,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
