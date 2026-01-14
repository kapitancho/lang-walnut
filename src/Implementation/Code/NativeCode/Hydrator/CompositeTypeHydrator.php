<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;

final readonly class CompositeTypeHydrator {
	public function __construct(
		private Hydrator      $hydrator,
		private ValueRegistry $valueRegistry,
	) {}


	public function hydrateProxyNamed(Value $value, ProxyNamedType $targetType, string $hydrationPath): Value {
		return $this->hydrator->hydrate($value, $targetType->actualType, $hydrationPath);
	}

	public function hydrateIntersection(Value $value, IntersectionType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			"Intersection type values cannot be hydrated"
		);
	}


	public function hydrateUnion(Value $value, UnionType $targetType, string $hydrationPath): Value {
		$exceptions = [];
		foreach($targetType->types as $type) {
			try {
				return $this->hydrator->hydrate($value, $type, $hydrationPath);
			} catch (HydrationException $ex) {
				$exceptions[] = $ex;
			}
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf(
				"The value could not be hydrated to any of the union types. The following errors occurred: \n%s",
				implode(array_map(
					fn(HydrationException $ex): string => sprintf(
						"- %s\n",
						$ex->errorMessage
					),
					$exceptions
				))
			)
		);
	}

	public function hydrateAlias(Value $value, AliasType $targetType, string $hydrationPath): Value {
		return $this->hydrator->hydrate($value, $targetType->aliasedType, $hydrationPath);
	}

	public function hydrateMutable(Value $value, MutableType $targetType, string $hydrationPath): MutableValue {
		return $this->valueRegistry->mutable(
			$targetType->valueType,
			$this->hydrator->hydrate($value, $targetType->valueType, $hydrationPath)
		);
	}

	public function hydrateArray(Value $value, ArrayType $targetType, string $hydrationPath): TupleValue {
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
				)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $seq => $item) {
					$result[] = $this->hydrator->hydrate($item, $refType, "{$hydrationPath}[$seq]");
				}
				return $this->valueRegistry->tuple($result);
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The array value should be with a length between %s and %s",
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be an array with a length between %s and %s",
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
			)
		);
	}

	public function hydrateSet(Value $value, SetType $targetType, string $hydrationPath): SetValue {
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$refType = $targetType->itemType;
			$result = [];
			foreach($value->values as $seq => $item) {
				$result[] = $this->hydrator->hydrate($item, $refType, "{$hydrationPath}[$seq]");
			}
			$set = $this->valueRegistry->set($result);
			$l = count($set->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
				)) {
				return $this->valueRegistry->set($result);
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The set value should be with a length between %s and %s",
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be an set with a length between %s and %s",
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
			)
		);
	}

	public function hydrateTuple(Value $value, TupleType $targetType, string $hydrationPath): TupleValue {
		if ($value instanceof TupleValue) {
			$l = count($targetType->types);
			if ($targetType->restType instanceof NothingType && count($value->values) > $l) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("The tuple value should be with %d items", $l)
				);
			}
			$result = [];
			foreach($targetType->types as $seq => $refType) {
				try {
					$item = $value->valueOf($seq);
					$result[] = $this->hydrator->hydrate($item, $refType, "{$hydrationPath}[$seq]");
				} catch (UnknownProperty) {
					throw new HydrationException(
						$value,
						$hydrationPath,
						sprintf("The tuple value should contain the index %d", $seq)
					);
				}
			}
			foreach($value->values as $seq => $val) {
				if (!isset($result[$seq])) {
					$result[] = $this->hydrator->hydrate($val,
						$targetType->types[$seq] ?? $targetType->restType, "{$hydrationPath}[$seq]");
				}
			}
			return $this->valueRegistry->tuple($result);

		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a tuple with %d items",
				count($targetType->types),
			)
		);
	}

	public function hydrateMap(Value $value, MapType $targetType, string $hydrationPath): RecordValue {
		if ($value instanceof RecordValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
				)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $key => $item) {
					$result[$key] = $this->hydrator->hydrate($item, $refType, "$hydrationPath.$key");
				}
				return $this->valueRegistry->record($result);
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The map value should be with a length between %s and %s",
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a map with a length between %s and %s",
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
			)
		);
	}

	public function hydrateRecord(Value $value, RecordType $targetType, string $hydrationPath): RecordValue {
		if ($value instanceof RecordValue) {
			$usedKeys = [];
			$result = [];
			foreach($targetType->types as $key => $refType) {
				$isOptional = false;
				if ($refType instanceof OptionalKeyType) {
					$isOptional = true;
					$refType = $refType->valueType;
				}
				try {
					$item = $value->valueOf($key);
					$result[$key] = $this->hydrator->hydrate($item, $refType, "$hydrationPath.$key");
					$usedKeys[$key] = true;
				} catch (UnknownProperty) {
					if (!$isOptional) {
						throw new HydrationException(
							$value,
							$hydrationPath,
							sprintf("The record value should contain the key %s", $key)
						);
					}
				}
			}
			foreach($value->values as $key => $val) {
				if (!($usedKeys[$key] ?? null)) {
					if ($targetType->restType instanceof NothingType) {
						throw new HydrationException(
							$value,
							$hydrationPath,
							sprintf("The record value may not contain the key %s", $key)
						);
					}
					$result[$key] = $this->hydrator->hydrate($val, $targetType->restType, "$hydrationPath.$key");
				}
			}
			return $this->valueRegistry->record( $result);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a record with %d items",
				count($targetType->types),
			)
		);
	}

	public function hydrateResult(Value $value, ResultType $targetType, string $hydrationPath): Value {
		try {
			return $this->hydrator->hydrate($value, $targetType->returnType, $hydrationPath);
		} catch (HydrationException $ex) {
			try {
				return $this->valueRegistry->error(
					$this->hydrator->hydrate($value, $targetType->errorType, $hydrationPath)
				);
			} catch (HydrationException) {
				throw $ex;
			}
		}
	}

}