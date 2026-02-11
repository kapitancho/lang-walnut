<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Enumeration;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<EnumerationSubsetType|MetaType, NullType, EnumerationValue, NullValue> */
final readonly class Enumeration extends NativeMethod {

	protected function getValidator(): callable {
		return function (EnumerationSubsetType|MetaType $targetType, NullType $parameterType): TypeType|ValidationFailure {
			return $this->typeRegistry->type(
				$targetType instanceof MetaType ?
					$this->typeRegistry->metaType(MetaTypeValue::Enumeration) :
					$targetType->enumeration
			);
		};
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		if ($targetType instanceof MetaType && $targetType->value !== MetaTypeValue::Enumeration) {
			return false;
		}
		return true;
	}

	protected function getExecutor(): callable {
		return fn(EnumerationValue $target, NullValue $parameter): TypeValue =>
			$this->valueRegistry->type($target->enumeration);
	}
}