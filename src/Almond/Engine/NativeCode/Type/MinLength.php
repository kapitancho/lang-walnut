<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<StringType|ArrayType|MapType|SetType, NullType, NullValue> */
final readonly class MinLength extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof StringType || $refType instanceof ArrayType ||
			$refType instanceof MapType || $refType instanceof SetType;
	}

	protected function getValidator(): callable {
		return fn(TypeType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integer(0);
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): IntegerValue {
			/** @var StringType|ArrayType|MapType|SetType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->integer($typeValue->range->minLength);
		};
	}

}
