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
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MutableType, Type, MutableValue, Value> */
final readonly class REMOVE extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof SetType && (int)(string)$valueType->range->minLength === 0) {
				return true;
			}
			if ($valueType instanceof RecordType) {
				if (
					!$valueType->restType instanceof NothingType ||
					array_any($valueType->types, fn(Type $type) => $type instanceof OptionalKeyType)
				) {
					return true;
				}
			}
			if ($valueType instanceof MapType && (int)(string)$valueType->range->minLength === 0) {
				return true;
			}
		}
		return false;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
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
							} else {
								return $this->validationFactory->error(
									ValidationErrorType::invalidParameterType,
									sprintf("[%s] Invalid parameter type: %s. Cannot remove map value with key %s",
										__CLASS__, $parameterType, $subsetValue
									),
									$origin
								);
							}
						} else {
							$useRestType = true;
						}
						if ($useRestType) {
							if ($valueType->restType instanceof NothingType) {
								return $this->validationFactory->error(
									ValidationErrorType::invalidParameterType,
									sprintf("[%s] Invalid parameter type: %s. Cannot remove map value with key %s",
										__CLASS__, $parameterType, $subsetValue
									),
									$origin
								);
							}
							$returnTypes[] = $valueType->restType;
						}
						return $this->typeRegistry->result(
							$this->typeRegistry->union($returnTypes),
							$this->typeRegistry->core->mapItemNotFound
						);
					}
				}
				if ($parameterType instanceof StringType) {
					return $this->typeRegistry->result(
						$valueType->asMapType()->itemType,
						$this->typeRegistry->core->mapItemNotFound
					);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					$origin
				);
			}
			if ($valueType instanceof MapType) {
				$pType = $this->toBaseType($parameterType);
				if ($pType->isSubtypeOf($valueType->keyType)) {
					return $this->typeRegistry->result(
						$valueType->itemType,
						$this->typeRegistry->core->mapItemNotFound
					);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					$origin
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
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
				($targetType instanceof MapType || $targetType instanceof RecordType) &&
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
