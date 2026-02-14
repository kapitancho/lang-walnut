<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
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
final readonly class WithoutByKey extends NativeMethod {
	use SubsetTypeHelper;

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof RecordType || $targetType instanceof MapType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, StringType $parameterType, mixed $origin): Type|ValidationFailure {
			$r = $this->typeRegistry;
			if ($targetType instanceof RecordType) {
				if ($parameterType instanceof StringSubsetType) {
					$recordTypes = $targetType->types;
					if (
						count($parameterType->subsetValues) === 1 &&
						array_key_exists($parameterType->subsetValues[0], $recordTypes)
					) {
						$elementType = $recordTypes[$parameterType->subsetValues[0]];
						unset($recordTypes[$parameterType->subsetValues[0]]);
						return $r->record([
							'element' => $elementType,
							'map' => $r->record(
								$recordTypes,
								$targetType->restType
							)
						], null);
					} else {
						$canBeMissing = false;
						$elementTypes = [];
						foreach ($parameterType->subsetValues as $subsetValue) {
							if (array_key_exists($subsetValue, $recordTypes)) {
								if ($recordTypes[$subsetValue] instanceof OptionalKeyType) {
									$elementTypes[] = $recordTypes[$subsetValue]->valueType;
									$canBeMissing = true;
								} else {
									$elementTypes[] = $recordTypes[$subsetValue];
									$recordTypes[$subsetValue] = $r->optionalKey(
										$recordTypes[$subsetValue]
									);
								}
							} else {
								$canBeMissing = true;
								$elementTypes[] = $targetType->restType;
							}
						}
						$returnType = $r->record([
							'element' => $r->union($elementTypes),
							'map' => $r->record(
								array_map(
									fn(Type $type): OptionalKeyType => $type instanceof OptionalKeyType ?
										$type :
										$r->optionalKey($type),
									$targetType->types
								),
								$targetType->restType
							)
						], null);
						return $canBeMissing ? $r->result(
							$returnType,
							$r->core->mapItemNotFound
						) : $returnType;
					}
				}
				$targetType = $targetType->asMapType();
			}
			$keyType = $targetType->keyType;
			if ($keyType instanceof StringSubsetType && $parameterType instanceof StringSubsetType) {
				$keyType = $this->stringSubsetDiff($r, $keyType, $parameterType);
			}
			$returnType = $r->record([
				'element' => $targetType->itemType,
				'map' => $r->map(
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
				)
			], null);
			return $r->result(
				$returnType,
				$r->core->mapItemNotFound
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
			$val = $values[$parameter->literalValue];
			unset($values[$parameter->literalValue]);
			return $this->valueRegistry->record([
				'element' => $val,
				'map' => $this->valueRegistry->record($values)
			]);
		};
	}

}
