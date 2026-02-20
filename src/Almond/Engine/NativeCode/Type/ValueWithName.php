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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<MetaType|EnumerationSubsetType, StringType, Value> */
final readonly class ValueWithName extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return ($targetRefType instanceof MetaType &&
			($targetRefType->value === MetaTypeValue::Enumeration || $targetRefType->value === MetaTypeValue::EnumerationSubset)) ||
			$targetRefType instanceof EnumerationSubsetType ?
			null : sprintf("Target ref type must be an EnumerationSubset type, got: %s", $targetRefType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, StringType $parameterType): ResultType {
			$refType = $targetType->refType;
			if ($refType instanceof MetaType) {
				return $this->typeRegistry->result(
					$this->typeRegistry->any,
					$this->typeRegistry->core->unknownEnumerationValue
				);
			}
			/** @var EnumerationSubsetType $refType */
			return $this->typeRegistry->result(
				$refType->enumeration,
				$this->typeRegistry->core->unknownEnumerationValue
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
