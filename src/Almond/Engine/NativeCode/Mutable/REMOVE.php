<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<MapType|SetType, Type, Value> */
final readonly class REMOVE extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		if ($valueType instanceof SetType && (int)(string)$valueType->range->minLength === 0) {
			return null;
		}
		if ($valueType instanceof RecordType) {
			if (
				!$valueType->restType instanceof NothingType ||
				array_any($valueType->types, fn(Type $type) => $type instanceof OptionalKeyType)
			) {
				return null;
			}
		}
		if ($valueType instanceof MapType && (int)(string)$valueType->range->minLength === 0) {
			return null;
		}
		return sprintf(
			"The value type of the target must be a Set type with minimum number of elements 0, a Record type with at least one optional key or an open Record type, or a Map type with minimum number of elements 0, got %s",
			$valueType
		);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		$valueType = $this->toBaseType($targetType->valueType);
		if ($valueType instanceof SetType) {
			return null;
		}
		if ($valueType instanceof RecordType) {
			if ($parameterType instanceof StringSubsetType) {
				$useRestType = false;
				foreach($parameterType->subsetValues as $subsetValue) {
					$rt = $valueType->types[$subsetValue] ?? null;
					if ($rt) {
						if (!$rt instanceof OptionalKeyType) {
							return sprintf(
								"Cannot remove required record key '%s' of type %s",
								$subsetValue, $rt
							);
						}
					} else {
						$useRestType = true;
					}
					if ($useRestType) {
						if ($valueType->restType instanceof NothingType) {
							return sprintf(
								"Cannot remove unknown record key '%s' from a closed record type",
								$subsetValue
							);
						}
					}
					return null;
				}
			}
			return $parameterType instanceof StringType ?
				null : sprintf(
					"The parameter type %s is not a valid key type for the record type, expected String",
					$parameterType
				);
		}
		if ($valueType instanceof MapType) {
			$pType = $this->toBaseType($parameterType);
			return $pType->isSubtypeOf($valueType->keyType) ?
				null : sprintf(
				"The parameter type %s is not a subtype of the map key type %s",
				$parameterType,
				$valueType->keyType
			);
		}
		return sprintf(
			"The mutable value type %s does not support the REMOVE operation",
			$valueType
		);
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			/** @var SetType|MapType $valueType */
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof SetType) {
				return $this->typeRegistry->result(
					$valueType->itemType,
					$this->typeRegistry->core->itemNotFound
				);
			}
			if ($valueType instanceof RecordType) {
				if ($parameterType instanceof StringSubsetType) {
					$useRestType = false;
					$returnTypes = [];
					foreach($parameterType->subsetValues as $subsetValue) {
						$rt = $valueType->types[$subsetValue] ?? null;
						if ($rt) {
							if ($rt instanceof OptionalKeyType) {
								$returnTypes[] = $rt->valueType;
							}
						} else {
							$useRestType = true;
						}
						if ($useRestType) {
							$returnTypes[] = $valueType->restType;
						}
						return $this->typeRegistry->result(
							$this->typeRegistry->union($returnTypes),
							$this->typeRegistry->core->mapItemNotFound
						);
					}
				}
			}
			return $this->typeRegistry->result(
				$valueType->itemType,
				$this->typeRegistry->core->mapItemNotFound
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): Value {
			$targetType = $this->toBaseType($target->targetType);
			if ($targetType instanceof SetType && $target->value instanceof SetValue) {
				$k = (string)$parameter;
				$vs = $target->value->valueSet;
				if (array_key_exists($k, $vs)) {
					unset($vs[$k]);
					$target->value = $this->valueRegistry->set(array_values($vs));
					return $parameter;
				}
				return $this->valueRegistry->error(
					$this->valueRegistry->core->itemNotFound
				);
			}
			if (
				$targetType instanceof MapType &&
				$target->value instanceof RecordValue &&
				$parameter instanceof StringValue
			) {
				$k = $parameter->literalValue;
				$rv = $target->value->values;
				if (array_key_exists($k, $rv)) {
					$item = $rv[$k];
					unset($rv[$k]);
					$target->value = $this->valueRegistry->record($rv);
					return $item;
				}
				return $this->valueRegistry->error(
					$this->valueRegistry->core->mapItemNotFound(
						$this->valueRegistry->record([
							'key' => $this->valueRegistry->string($k)
						])
					)
				);
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
