<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<FunctionType|ResultType|MetaType, NullType, NullValue> */
final readonly class ReturnType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof FunctionType || $refType instanceof ResultType ||
			($refType instanceof MetaType && $refType->value === MetaTypeValue::Function);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): TypeType {
			/** @var FunctionType|ResultType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof FunctionType || $refType instanceof ResultType) {
				return $this->typeRegistry->type($refType->returnType);
			}
			return $this->typeRegistry->type($this->typeRegistry->any);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TypeValue {
			/** @var FunctionType|ResultType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->type($typeValue->returnType);
		};
	}

}
