<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\SubsetTypeHelper;

/** @extends NativeMethod<MapType|RecordType, StringType, RecordValue, StringValue> */
final readonly class ValuesWithoutKey extends NativeMethod {
	use SubsetTypeHelper;

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof RecordType || $targetType instanceof MapType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, StringType $parameterType): Type {
			$targetType = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
			$keyType = $targetType->keyType;
			if ($keyType instanceof StringSubsetType && $parameterType instanceof StringSubsetType) {
				$keyType = $this->stringSubsetDiff($this->typeRegistry, $keyType, $parameterType);
			}
			return $this->typeRegistry->result(
				$this->typeRegistry->map(
					$targetType->itemType,
					$targetType->range->maxLength === PlusInfinity::value ?
						$targetType->range->minLength : max(0,
						min(
							$targetType->range->minLength - 1,
							$targetType->range->maxLength - 1
						)),
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($targetType->range->maxLength - 1, 0),
					$keyType
				),
				$this->typeRegistry->core->mapItemNotFound
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, StringValue $parameter): Value {
			$values = $target->values;
			if (!isset($values[$parameter->literalValue])) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->mapItemNotFound(
						$this->valueRegistry->record(['key' => $parameter])
					)
				);
			}
			unset($values[$parameter->literalValue]);
			return $this->valueRegistry->record($values);
		};
	}

}
