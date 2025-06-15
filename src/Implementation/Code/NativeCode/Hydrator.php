<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class Hydrator {
	public function __construct(
		private ProgramRegistry $programRegistry,
	) {}

	/** @throws HydrationException */
	public function hydrate(Value $value, Type $targetType, string $hydrationPath): Value {
		/** @var callable-string $fn */
		$fn = match(true) {
			$targetType instanceof BooleanType => $this->hydrateBoolean(...),
			$targetType instanceof FalseType => $this->hydrateFalse(...),
			$targetType instanceof NullType => $this->hydrateNull(...),
			$targetType instanceof TrueType => $this->hydrateTrue(...),

			$targetType instanceof AnyType => $this->hydrateAny(...),
			$targetType instanceof NothingType => $this->hydrateNothing(...),
			$targetType instanceof FunctionType => $this->hydrateFunction(...),
			$targetType instanceof ArrayType => $this->hydrateArray(...),
			$targetType instanceof SetType => $this->hydrateSet(...),
			$targetType instanceof AtomType => $this->hydrateAtom(...),
			$targetType instanceof EnumerationType => $this->hydrateEnumeration(...),
			$targetType instanceof EnumerationSubsetType => $this->hydrateEnumerationSubset(...),
			$targetType instanceof IntegerType => $this->hydrateInteger(...),
			$targetType instanceof IntegerSubsetType => $this->hydrateIntegerSubset(...),
			$targetType instanceof IntersectionType => $this->hydrateIntersection(...),
			$targetType instanceof MapType => $this->hydrateMap(...),
			$targetType instanceof MutableType => $this->hydrateMutable(...),
			$targetType instanceof RealType => $this->hydrateReal(...),
			$targetType instanceof RealSubsetType => $this->hydrateRealSubset(...),
			$targetType instanceof RecordType => $this->hydrateRecord(...),
			$targetType instanceof StringType => $this->hydrateString(...),
			$targetType instanceof StringSubsetType => $this->hydrateStringSubset(...),
			$targetType instanceof TupleType => $this->hydrateTuple(...),
			$targetType instanceof TypeType => $this->hydrateType(...),
			$targetType instanceof UnionType => $this->hydrateUnion(...),
			$targetType instanceof AliasType => $this->hydrateAlias(...),
			$targetType instanceof ResultType => $this->hydrateResult(...),
			$targetType instanceof DataType => $this->hydrateData(...),
			$targetType instanceof OpenType => $this->hydrateOpen(...),
			$targetType instanceof SealedType => $this->hydrateSealed(...),
			// @codeCoverageIgnoreStart
			default => throw new HydrationException(
				$value,
				$hydrationPath,
				"Unsupported type"
			)
			// @codeCoverageIgnoreEnd
		};
		return $fn($value, $targetType, $hydrationPath);
	}

	private function hydrateAny(Value $value, AnyType $targetType, string $hydrationPath): Value {
		return $value;
	}

	private function hydrateNothing(Value $value, NothingType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("There is no value allowed, %s provided", $value)
		);
	}

	private function hydrateFunction(Value $value, FunctionType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			"Functions cannot be hydrated"
		);
	}

	private function hydrateIntersection(Value $value, IntersectionType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			"Intersection type values cannot be hydrated"
		);
	}

	private function hydrateType(Value $value, TypeType $targetType, string $hydrationPath): TypeValue {
		if ($value instanceof StringValue) {
			try {
				$typeName = $value->literalValue;
				$type = match($typeName) {
					'Any' => $this->programRegistry->typeRegistry->any,
					'Nothing' => $this->programRegistry->typeRegistry->nothing,
					'Array' => $this->programRegistry->typeRegistry->array(),
					'Map' => $this->programRegistry->typeRegistry->map(),
					'Mutable' => $this->programRegistry->typeRegistry->mutable($this->programRegistry->typeRegistry->any),
					'Type' => $this->programRegistry->typeRegistry->type($this->programRegistry->typeRegistry->any),
					'Null' => $this->programRegistry->typeRegistry->null,
					'True' => $this->programRegistry->typeRegistry->true,
					'False' => $this->programRegistry->typeRegistry->false,
					'Boolean' => $this->programRegistry->typeRegistry->boolean,
					'Integer' => $this->programRegistry->typeRegistry->integer(),
					'Real' => $this->programRegistry->typeRegistry->real(),
					'String' => $this->programRegistry->typeRegistry->string(),
					default => $this->programRegistry->typeRegistry->withName(new TypeNameIdentifier($typeName)),
				}				;
				//$type = $this->programRegistry->typeRegistry->withName(new TypeNameIdentifier());
				if ($type->isSubtypeOf($targetType->refType)) {
					return $this->programRegistry->valueRegistry->type($type);
				}
				// Should not be reachable
				// @codeCoverageIgnoreStart
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("The type should be a subtype of %s", $targetType->refType)
				);
				// @codeCoverageIgnoreEnd
			} catch (UnknownType) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					"The string value should be a name of a valid type"
				);
			}
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be a string, containing a name of a valid type"
		);
	}

	private function hydrateAtom(Value $value, AtomType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Atom hydration failed. Error: %s"
		) ?? $targetType->value;
	}

	private function hydrateEnumeration(Value $value, EnumerationType $targetType, string $hydrationPath): Value {
		return $this->hydrateEnumerationSubset($value, $targetType, $hydrationPath);
	}

	private function hydrateEnumerationSubset(Value $value, EnumerationSubsetType $targetType, string $hydrationPath): EnumerationValue {
		$result = $this->tryJsonValueCast(
			$targetType->enumeration, $value, $hydrationPath,
			"Enumeration hydration failed. Error: %s"
		);
		if ($result) {
			foreach($targetType->subsetValues as $enumValue) {
				if ($enumValue === $result) {
					return $result;
				}
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The enumeration value %s is not among %s",
					$result,
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		if ($value instanceof StringValue) {
			foreach($targetType->subsetValues as $enumValue) {
				if ($enumValue->name->identifier === $value->literalValue) {
					return $this->programRegistry->valueRegistry->enumerationValue(
						$targetType->enumeration->name,
						$enumValue->name
					);
				}
			}
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string with a value among %s",
				implode(', ', $targetType->subsetValues)
			)
		);
	}

	private function hydrateUnion(Value $value, UnionType $targetType, string $hydrationPath): Value {
		$exceptions = [];
		foreach($targetType->types as $type) {
			try {
				return $this->hydrate($value, $type, $hydrationPath);
			} catch (HydrationException $ex) {
				$exceptions[] = $ex;
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		throw $exceptions[0];
	}

	private function hydrateAlias(Value $value, AliasType $targetType, string $hydrationPath): Value {
		return $this->hydrate($value, $targetType->aliasedType, $hydrationPath);
	}

	private function hydrateResult(Value $value, ResultType $targetType, string $hydrationPath): Value {
		try {
			return $this->hydrate($value, $targetType->returnType, $hydrationPath);
		} catch (HydrationException $ex) {
			try {
				return $this->programRegistry->valueRegistry->error(
					$this->hydrate($value, $targetType->errorType, $hydrationPath)
				);
			} catch (HydrationException) {
				throw $ex;
			}
		}
	}

	private function hydrateData(Value $value, OpenType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Open type hydration failed. Error: %s"
		) ?? $this->constructValue(
			$targetType,
			$this->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	private function hydrateOpen(Value $value, OpenType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Open type hydration failed. Error: %s"
		) ?? $this->constructValue(
			$targetType,
			$this->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	private function hydrateSealed(Value $value, SealedType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Sealed type hydration failed. Error: %s"
		) ?? $this->constructValue(
			$targetType,
			$this->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	private function tryJsonValueCast(
		NamedType $targetType,
		Value $value,
		string $hydrationPath,
		string $hydrationErrorMessage
	): Value|null {
		$method = $this->programRegistry->methodFinder->methodForType(
			$this->programRegistry->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->name)
			)
		);
		if ($method instanceof Method) {
			$result = $method->execute(
				$this->programRegistry,
				($value),
				($this->programRegistry->valueRegistry->null)
			);
			$resultValue = $result;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf($hydrationErrorMessage, $resultValue->errorValue)
				);
			}
			return $resultValue;
		}
		return null;
	}

	private function constructValue(Type $targetType, Value $baseValue, string $hydrationPath): Value {
		$result = new ValueConstructor()->executeValidator(
			$this->programRegistry,
			$targetType,
			$baseValue
		);
		$resultValue = $result;
		if ($resultValue instanceof ErrorValue) {
			throw new HydrationException(
				$baseValue,
				$hydrationPath,
				sprintf('Value construction failed. Error: %s', $resultValue->errorValue)
			);
		}
		return $resultValue;
	}

	private function hydrateMutable(Value $value, MutableType $targetType, string $hydrationPath): MutableValue {
		return $this->programRegistry->valueRegistry->mutable(
			$targetType->valueType,
			$this->hydrate($value, $targetType->valueType, $hydrationPath)
		);
	}

	private function hydrateInteger(Value $value, IntegerType $targetType, string $hydrationPath): IntegerValue {
		if ($value instanceof IntegerValue) {
			if ((
				$targetType->range->minValue === MinusInfinity::value ||
				$targetType->range->minValue <= $value->literalValue
			) && (
					$targetType->range->maxValue === PlusInfinity::value ||
					$targetType->range->maxValue >= $value->literalValue
			)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The integer value should be in the range %s..%s",
					$targetType->range->minValue === MinusInfinity::value ? "-Infinity" : $targetType->range->minValue,
					$targetType->range->maxValue === PlusInfinity::value ? "+Infinity" : $targetType->range->maxValue,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be an integer in the range %s..%s",
				$targetType->range->minValue === MinusInfinity::value ? "-Infinity" : $targetType->range->minValue,
				$targetType->range->maxValue === PlusInfinity::value ? "+Infinity" : $targetType->range->maxValue,
			)
		);
	}

	private function hydrateIntegerSubset(Value $value, IntegerSubsetType $targetType, string $hydrationPath): IntegerValue {
		if ($value instanceof IntegerValue) {
			if ($targetType->contains($value)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The integer value should be among %s",
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be an integer among %s",
				implode(', ', $targetType->subsetValues)
			)
		);
	}

	private function hydrateBoolean(Value $value, BooleanType $targetType, string $hydrationPath): BooleanValue {
		if ($value instanceof BooleanValue) {
			return $value;
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be a boolean"
		);
	}

	private function hydrateNull(Value $value, NullType $targetType, string $hydrationPath): NullValue {
		if ($value instanceof NullValue) {
			return $value;
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be 'null'"
		);
	}

	private function hydrateTrue(Value $value, TrueType $targetType, string $hydrationPath): BooleanValue {
		if ($value instanceof BooleanValue) {
			if ($value->literalValue === true) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				"The boolean value should be true"
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be 'true'"
		);
	}

	private function hydrateFalse(Value $value, FalseType $targetType, string $hydrationPath): BooleanValue {
		if ($value instanceof BooleanValue) {
			if ($value->literalValue === false) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				"The boolean value should be false"
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be 'false'"
		);
	}

	private function hydrateString(Value $value, StringType $targetType, string $hydrationPath): StringValue {
		if ($value instanceof StringValue) {
			$l = mb_strlen($value->literalValue);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The string value should be with a length between %s and %s",
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string with a length between %s and %s",
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
			)
		);
	}

	private function hydrateStringSubset(Value $value, StringSubsetType $targetType, string $hydrationPath): StringValue {
		if ($value instanceof StringValue) {
			if ($targetType->contains($value)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The string value should be among %s",
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string among %s",
				implode(', ', $targetType->subsetValues)
			)
		);
	}

	private function hydrateArray(Value $value, ArrayType $targetType, string $hydrationPath): TupleValue {
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $seq => $item) {
					$result[] = $this->hydrate($item, $refType, "{$hydrationPath}[$seq]");
				}
				return $this->programRegistry->valueRegistry->tuple($result);
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

	private function hydrateSet(Value $value, SetType $targetType, string $hydrationPath): SetValue {
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$refType = $targetType->itemType;
			$result = [];
			foreach($value->values as $seq => $item) {
				$result[] = $this->hydrate($item, $refType, "{$hydrationPath}[$seq]");
			}
			$set = $this->programRegistry->valueRegistry->set($result);
			$l = count($set->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				return $this->programRegistry->valueRegistry->set($result);
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

	private function hydrateTuple(Value $value, TupleType $targetType, string $hydrationPath): TupleValue {
		if ($value instanceof TupleValue) {
			$l = count($targetType->types);
			if ($targetType->restType instanceof NothingType && count($value->values) > $l) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("The tuple value should be with %d items",$l)
				);
			}
			$result = [];
			foreach($targetType->types as $seq => $refType) {
				try {
					$item = $value->valueOf($seq);
					$result[] = $this->hydrate($item, $refType, "{$hydrationPath}[$seq]");
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
					$result[] = $this->hydrate($val,
						$targetType->types[$seq] ?? $targetType->restType, "{$hydrationPath}[$seq]");
				}
			}
			return $this->programRegistry->valueRegistry->tuple($result);

		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a tuple with %d items",
				count($targetType->types),
			)
		);
	}

	private function hydrateMap(Value $value, MapType $targetType, string $hydrationPath): RecordValue {
		if ($value instanceof RecordValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $key => $item) {
					$result[$key] = $this->hydrate($item, $refType, "$hydrationPath.$key");
				}
				return $this->programRegistry->valueRegistry->record($result);
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

	private function hydrateRecord(Value $value, RecordType $targetType, string $hydrationPath): RecordValue {
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
					$result[$key] = $this->hydrate($item, $refType, "$hydrationPath.$key");
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
					$result[$key] = $this->hydrate($val, $targetType->restType, "$hydrationPath.$key");
				}
			}
			return $this->programRegistry->valueRegistry->record( $result);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a record with %d items",
				count($targetType->types),
			)
		);
	}

	private function hydrateReal(Value $value, RealType $targetType, string $hydrationPath): RealValue {
		if ($value instanceof IntegerValue || $value instanceof RealValue) {
			if ((
				$targetType->range->minValue === MinusInfinity::value ||
				$targetType->range->minValue <= $value->literalValue
			) && (
					$targetType->range->maxValue === PlusInfinity::value ||
					$targetType->range->maxValue >= $value->literalValue
			)) {
				return $this->programRegistry->valueRegistry->real((float)(string)$value->literalValue);
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The real value should be in the range %s..%s",
					$targetType->range->minValue === MinusInfinity::value ? "-Infinity" : $targetType->range->minValue,
					$targetType->range->maxValue === PlusInfinity::value ? "+Infinity" : $targetType->range->maxValue,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a real number in the range %s..%s",
				$targetType->range->minValue === MinusInfinity::value ? "-Infinity" : $targetType->range->minValue,
				$targetType->range->maxValue === PlusInfinity::value ? "+Infinity" : $targetType->range->maxValue,
			)
		);
	}

	private function hydrateRealSubset(Value $value, RealSubsetType $targetType, string $hydrationPath): RealValue {
		if ($value instanceof IntegerValue) {
			$value = $value->asRealValue();
		}
		if ($value instanceof RealValue) {
			if ($targetType->contains($value)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The real value should be among %s",
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a real number among %s",
				implode(', ', $targetType->subsetValues)
			)
		);
	}

}