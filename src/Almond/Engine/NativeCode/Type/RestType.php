<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

/** @extends TypeNativeMethod<TupleType|RecordType|MetaType, NullType, NullValue> */
final readonly class RestType extends TypeNativeMethod {

	protected function getValidator(): callable {
		return function (TypeType $targetType, NullType $parameterType): TypeType {
			/** @var TupleType|RecordType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->type(
				$refType instanceof TupleType || $refType instanceof RecordType ?
					$refType->restType : $this->typeRegistry->any
			);
		};
	}

	protected function getExecutor(): callable {
		return function (TypeValue $target, NullValue $parameter): TypeValue {
			/** @var TupleType|RecordType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->type($typeValue->restType);
		};
	}

	protected function isTargetRefTypeValid(Type $targetRefType, Expression|null $origin): bool {
		return $targetRefType instanceof TupleType || $targetRefType instanceof RecordType ||
			($targetRefType instanceof MetaType &&
				(
					$targetRefType->value === MetaTypeValue::Tuple ||
					$targetRefType->value === MetaTypeValue::Record
				)
			);
	}
}
