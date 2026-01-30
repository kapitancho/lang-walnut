<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\CompositeTypeHydrator as CompositeTypeHydratorInterface;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\HydrationException;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\Hydrator;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\CompositeType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class CompositeTypeHydrator implements CompositeTypeHydratorInterface {
	public function __construct(
		private ValueRegistry $valueRegistry,
	) {}
	
	/** @throws HydrationException */
	public function hydrate(Hydrator $hydrator, Value $value, CompositeType $targetType, string $hydrationPath): Value {
		/** @phpstan-ignore-next-line var.type */
		$fn = match(true) {
			$targetType instanceof ArrayType => $this->hydrateArray(...),
			$targetType instanceof FunctionType => $this->hydrateFunction(...),
			$targetType instanceof SetType => $this->hydrateSet(...),
			$targetType instanceof IntersectionType => $this->hydrateIntersection(...),
			$targetType instanceof ShapeType => $this->hydrateShape(...),
			$targetType instanceof OptionalKeyType => $this->hydrateOptionalKey(...),
			$targetType instanceof MapType => $this->hydrateMap(...),
			$targetType instanceof MutableType => $this->hydrateMutable(...),
			$targetType instanceof RecordType => $this->hydrateRecord(...),
			$targetType instanceof TupleType => $this->hydrateTuple(...),
			$targetType instanceof UnionType => $this->hydrateUnion(...),
			$targetType instanceof ResultType => $this->hydrateResult(...),
			$targetType instanceof ProxyNamedType => $this->hydrateProxyNamed(...),
			// @codeCoverageIgnoreStart
			default => throw new HydrationException(
				$value,
				$hydrationPath,
				"Unsupported type: " . $targetType::class
			)
			// @codeCoverageIgnoreEnd
		};
		/** @phpstan-ignore-next-line argument.type */
		return $fn($hydrator, $value, $targetType, $hydrationPath);
	}

	private function hydrateProxyNamed(Hydrator $hydrator, Value $value, ProxyNamedType $targetType, string $hydrationPath): Value {
		return $hydrator->hydrate($value, $targetType->actualType, $hydrationPath);
	}

	private function hydrateIntersection(Hydrator $hydrator, Value $value, IntersectionType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			"Intersection type values cannot be hydrated"
		);
	}

	private function hydrateShape(Hydrator $hydrator, Value $value, ShapeType $targetType, string $hydrationPath): Value {
		return $hydrator->hydrate($value, $targetType->refType, $hydrationPath);
	}

	private function hydrateOptionalKey(Hydrator $hydrator, Value $value, OptionalKeyType $targetType, string $hydrationPath): Value {
		return $hydrator->hydrate($value, $targetType->valueType, $hydrationPath);
	}

	private function hydrateUnion(Hydrator $hydrator, Value $value, UnionType $targetType, string $hydrationPath): Value {
		$exceptions = [];
		foreach($targetType->types as $type) {
			try {
				return $hydrator->hydrate($value, $type, $hydrationPath);
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

	private function hydrateMutable(Hydrator $hydrator, Value $value, MutableType $targetType, string $hydrationPath): MutableValue {
		return $this->valueRegistry->mutable(
			$targetType->valueType,
			$hydrator->hydrate($value, $targetType->valueType, $hydrationPath)
		);
	}

	private function hydrateFunction(Hydrator $hydrator, Value $value, FunctionType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			"Functions cannot be hydrated"
		);
	}

	private function hydrateArray(Hydrator $hydrator, Value $value, ArrayType $targetType, string $hydrationPath): TupleValue {
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
				)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $seq => $item) {
					$result[] = $hydrator->hydrate($item, $refType, "{$hydrationPath}[$seq]");
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

	private function hydrateSet(Hydrator $hydrator, Value $value, SetType $targetType, string $hydrationPath): SetValue {
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$refType = $targetType->itemType;
			$result = [];
			foreach($value->values as $seq => $item) {
				$result[] = $hydrator->hydrate($item, $refType, "{$hydrationPath}[$seq]");
			}
			$set = $this->valueRegistry->set($result);
			$l = count($set->values);
			if ($targetType->range->minLength <= $l && (
				$targetType->range->maxLength === PlusInfinity::value ||
				$targetType->range->maxLength >= $l
			)) {
				return $set;
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

	private function hydrateTuple(Hydrator $hydrator, Value $value, TupleType $targetType, string $hydrationPath): TupleValue {
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
					$result[] = $hydrator->hydrate($item, $refType, "{$hydrationPath}[$seq]");
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
					$result[] = $hydrator->hydrate($val,
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

	private function hydrateMap(Hydrator $hydrator, Value $value, MapType $targetType, string $hydrationPath): RecordValue {
		if ($value instanceof RecordValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
				)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $key => $item) {
					$result[$key] = $hydrator->hydrate($item, $refType, "$hydrationPath.$key");
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

	private function hydrateRecord(Hydrator $hydrator, Value $value, RecordType $targetType, string $hydrationPath): RecordValue {
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
					$result[$key] = $hydrator->hydrate($item, $refType, "$hydrationPath.$key");
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
					$result[$key] = $hydrator->hydrate($val, $targetType->restType, "$hydrationPath.$key");
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

	private function hydrateResult(Hydrator $hydrator, Value $value, ResultType $targetType, string $hydrationPath): Value {
		try {
			return $hydrator->hydrate($value, $targetType->returnType, $hydrationPath);
		} catch (HydrationException $ex) {
			try {
				return $this->valueRegistry->error(
					$hydrator->hydrate($value, $targetType->errorType, $hydrationPath)
				);
			} catch (HydrationException) {
				throw $ex;
			}
		}
	}
}