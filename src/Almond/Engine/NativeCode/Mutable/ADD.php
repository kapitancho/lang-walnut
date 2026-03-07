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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<SetType|MapType, Type, Value> */
final readonly class ADD extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		if ($valueType instanceof SetType && $valueType->range->maxLength === PlusInfinity::value) {
			return null;
		}
		if ($valueType instanceof RecordType) {
			return null;
		}
		if ($valueType instanceof MapType && $valueType->range->maxLength === PlusInfinity::value) {
			return null;
		}
		return sprintf(
			"The value type of the target must be a Set, Record or Map type with an unbounded number of items, got %s",
			$valueType
		);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		$valueType = $this->toBaseType($targetType->valueType);
		$p = $this->toBaseType($parameterType);
		if ($valueType instanceof SetType) {
			return $p->isSubtypeOf($valueType->itemType) ?
				null : sprintf(
					"The parameter type %s is not a subtype of the set item type %s",
					$parameterType,
					$valueType->itemType
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
									return sprintf(
										"An item with key '%s' cannot be added to this record type",
										$subsetValue
									);
								} else {
									return sprintf(
										"The value type %s for key '%s' is not a subtype of %s",
										$vv, $subsetValue,
										$mType instanceof OptionalKeyType ? $mType->valueType : $mType
									);
								}
							}
						}
						return null;
					}
					if ($kk instanceof StringType) {
						if (!$vv->isSubtypeOf($valueType->restType)) {
							return sprintf(
								"The value type %s should be a subtype of the rest type %s",
								$vv, $valueType->restType
							);
						}
						foreach($valueType->types as $vKey => $vType) {
							if (!$valueType->restType->isSubtypeOf($vType)) {
								return sprintf(
									"The rest type %s is not a subtype of the type %s for key '%s'",
									$valueType->restType, $vType, $vKey
								);
							}
						}
						return null;
					}
				}
			}
			return sprintf(
				"The parameter type %s is not a valid key-value record for the record type",
				$parameterType
			);
		}
		if ($valueType instanceof MapType) {
			return $p->isSubtypeOf($this->typeRegistry->record([
				'key' => $valueType->keyType,
				'value' => $valueType->itemType
			], null)) ?
				null :
				 sprintf(
					 "The parameter type %s is not a valid key-value record for the map type %s",
					 $parameterType,
					 $valueType
				 );
		}
		return sprintf(
			"The mutable value type %s does not support the ADD operation",
			$valueType
		);
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, Type $parameterType, mixed $origin): MutableType => $targetType;
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
