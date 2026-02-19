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
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MutableType, Type, MutableValue, Value> */
final readonly class ADD extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator): bool|Type {
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof SetType && $valueType->range->maxLength === PlusInfinity::value) {
				return true;
			}
			if ($valueType instanceof RecordType) {
				return true;
			}
			if ($valueType instanceof MapType && $valueType->range->maxLength === PlusInfinity::value) {
				return true;
			}
		}
		return false;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$valueType = $this->toBaseType($targetType->valueType);
			$p = $this->toBaseType($parameterType);
			if ($valueType instanceof SetType) {
				if ($p->isSubtypeOf($valueType->itemType)) {
					return $targetType;
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s is not a subtype of the set item type %s",
						$parameterType,
						$valueType->itemType
					),
					$origin
				);
			}
			if ($valueType instanceof RecordType) {
				if ($p->isSubtypeOf($this->typeRegistry->record([
					'key' => $this->typeRegistry->string(),
					'value' => $this->typeRegistry->any
				], null))) {
					$kk = $p->types['key'] ?? null;
					$vv = $p->types['value'] ?? null;
					if ($kk && $vv) {
						if ($kk instanceof StringSubsetType) {
							foreach ($kk->subsetValues as $subsetValue) {
								$mType = $valueType->types[$subsetValue] ?? $valueType->restType;
								if (!$vv->isSubtypeOf($mType)) {
									if ($mType instanceof NothingType) {
										return $this->validationFactory->error(
											ValidationErrorType::invalidParameterType,
											sprintf(
												"An item with key '%s' cannot be added to this record type",
												$subsetValue
											),
											$origin
										);
									} else {
										return $this->validationFactory->error(
											ValidationErrorType::invalidParameterType,
											sprintf(
												"The value type %s for key '%s' is not a subtype of %s",
												$vv, $subsetValue,
												$mType instanceof OptionalKeyType ? $mType->valueType : $mType
											),
											$origin
										);
									}
								}
							}
							return $targetType;
						}
						if ($kk instanceof StringType) {
							if (!$vv->isSubtypeOf($valueType->restType)) {
								return $this->validationFactory->error(
									ValidationErrorType::invalidParameterType,
									sprintf(
										"The value type %s should be a subtype of the rest type %s",
										$vv, $valueType->restType
									),
									$origin
								);
							}
							foreach($valueType->types as $vKey => $vType) {
								if (!$valueType->restType->isSubtypeOf($vType)) {
									return $this->validationFactory->error(
										ValidationErrorType::invalidParameterType,
										sprintf(
											"The rest type %s is not a subtype of the type %s for key '%s'",
											$valueType->restType, $vType, $vKey
										),
										$origin
									);
								}
							}
							return $targetType;
						}
					}
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s is not a valid key-value record for the record type",
						$parameterType
					),
					$origin
				);
			}
			if ($valueType instanceof MapType) {
				if ($p->isSubtypeOf($this->typeRegistry->record([
					'key' => $valueType->keyType,
					'value' => $valueType->itemType
				], null))) {
					return $targetType;
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s is not a valid key-value record for the map type %s",
						$parameterType,
						$valueType
					),
					$origin
				);
			}
			// @codeCoverageIgnoreStart
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"The mutable value type %s does not support the ADD operation",
					$valueType
				),
				$origin
			);
			// @codeCoverageIgnoreEnd
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): MutableValue {
			$targetType = $this->toBaseType($target->targetType);
			$mv = $target->value;
			if ($targetType instanceof SetType && $mv instanceof SetValue) {
				$arr = $mv->values;
				$arr[] = $parameter;
				$target->value = $this->valueRegistry->set($arr);
				return $target;
			}
			if ($targetType instanceof RecordType && $mv instanceof RecordValue && $parameter instanceof RecordValue) {
				$kk = $parameter->values['key']->literalValue ?? null;
				$vv = $parameter->values['value'] ?? null;
				if ($kk && $vv) {
					if ($vv->type->isSubtypeOf($targetType->types[$kk] ?? $targetType->restType)) {
						$mv = $target->value->values;
						$mv[$kk] = $vv;
						$target->value = $this->valueRegistry->record($mv);
						return $target;
					}
				}
				return $target;
			}
			if ($targetType instanceof MapType && $mv instanceof RecordValue) {
				$recordType = $this->typeRegistry->record([
					'key' => $targetType->keyType,
					'value' => $targetType->itemType
				], null);
				if ($parameter->type->isSubtypeOf($recordType)) {
					$mv = $target->value->values;
					$mv[$parameter->values['key']->literalValue] = $parameter->values['value'];
					$target->value = $this->valueRegistry->record($mv);
					return $target;
				}
				return $target;
			}
			return $target;
		};
	}

}
