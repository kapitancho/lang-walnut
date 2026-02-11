<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<ResultType|FunctionType|MetaType, TypeType, TypeValue> */
final readonly class WithReturnType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof ResultType || $refType instanceof FunctionType ||
			($refType instanceof MetaType && $refType->value === MetaTypeValue::Function);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var ResultType|FunctionType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof ResultType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->result(
						$parameterType->refType,
						$refType->errorType,
					)
				);
			}
			if ($refType instanceof FunctionType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->function(
						$refType->parameterType,
						$parameterType->refType
					)
				);
			}
			return $this->typeRegistry->type(
				$this->typeRegistry->function(
					$this->typeRegistry->nothing,
					$parameterType->refType
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var ResultType|FunctionType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof ResultType) {
				$result = $this->typeRegistry->result(
					$parameter->typeValue,
					$typeValue->errorType,
				);
				return $this->valueRegistry->type($result);
			}
			/** @var FunctionType $typeValue */
			$result = $this->typeRegistry->function(
				$typeValue->parameterType,
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
