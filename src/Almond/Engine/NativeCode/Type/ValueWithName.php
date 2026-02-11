<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<MetaType|EnumerationSubsetType, Type, Value> */
final readonly class ValueWithName extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		return ($targetRefType instanceof MetaType &&
			($targetRefType->value === MetaTypeValue::Enumeration || $targetRefType->value === MetaTypeValue::EnumerationSubset)) ||
			$targetRefType instanceof EnumerationSubsetType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType, mixed $origin): ResultType|ValidationFailure {
			$refType = $targetType->refType;
			if ($refType instanceof MetaType) {
				return $this->typeRegistry->result(
					$this->typeRegistry->any,
					$this->typeRegistry->core->unknownEnumerationValue
				);
			}
			/** @var EnumerationSubsetType $refType */
			if ($parameterType instanceof StringType) {
				return $this->typeRegistry->result(
					$refType->enumeration,
					$this->typeRegistry->core->unknownEnumerationValue
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, StringValue $parameter): Value {
			/** @var EnumerationSubsetType $refType */
			$refType = $target->typeValue;
			return $refType->subsetValues[$parameter->literalValue] ??
				$this->valueRegistry->error(
					$this->valueRegistry->core->unknownEnumerationValue(
						$this->valueRegistry->record([
							'enumeration' => $this->valueRegistry->type($refType),
							'value' => $parameter,
						])
					)
				);
		};
	}
}
