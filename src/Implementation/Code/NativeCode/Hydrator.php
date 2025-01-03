<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class Hydrator {
	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
	) {}

	/** @throws HydrationException */
	public function hydrate(Value $value, Type $targetType, string $hydrationPath): TypedValue {
		$result = $this->hydrateValue($value, $targetType, $hydrationPath);
		return new TypedValue($targetType, $result);
	}

	/** @throws HydrationException */
	public function hydrateValue(Value $value, Type $targetType, string $hydrationPath): Value {
		/** @var callable-string $fn */
		$fn = match(true) {
			$targetType instanceof BooleanType => $this->hydrateBoolean(...),
			$targetType instanceof FalseType => $this->hydrateFalse(...),
			$targetType instanceof NullType => $this->hydrateNull(...),
			$targetType instanceof TrueType => $this->hydrateTrue(...),

			$targetType instanceof AnyType => $this->hydrateAny(...),
			$targetType instanceof ArrayType => $this->hydrateArray(...),
			$targetType instanceof AtomType => $this->hydrateAtom(...),
			$targetType instanceof EnumerationType => $this->hydrateEnumeration(...),
			$targetType instanceof EnumerationSubsetType => $this->hydrateEnumerationSubset(...),
			$targetType instanceof IntegerType => $this->hydrateInteger(...),
			$targetType instanceof IntegerSubsetType => $this->hydrateIntegerSubset(...),
			//$targetType instanceof IntersectionType => $this->hydrateIntersection(...),
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
			$targetType instanceof SubtypeType => $this->hydrateSubtype(...),
			$targetType instanceof SealedType => $this->hydrateSealed(...),
			default => $value
		};
		return is_callable($fn) ? $fn($value, $targetType, $hydrationPath) : $fn;
	}

	private function hydrateAny(Value $value, AnyType $targetType, string $hydrationPath): Value {
		return $value;
	}

	private function hydrateType(Value $value, TypeType $targetType, string $hydrationPath): TypeValue {
		if ($value instanceof StringValue) {
			try {
				$typeName = $value->literalValue;
				$type = match($typeName) {
					'Any' => $this->context->typeRegistry->any,
					'Nothing' => $this->context->typeRegistry->nothing,
					'Array' => $this->context->typeRegistry->array(),
					'Map' => $this->context->typeRegistry->map(),
					'Mutable' => $this->context->typeRegistry->mutable($this->context->typeRegistry->any),
					'Type' => $this->context->typeRegistry->type($this->context->typeRegistry->any),
					'Null' => $this->context->typeRegistry->null,
					'True' => $this->context->typeRegistry->true,
					'False' => $this->context->typeRegistry->false,
					'Boolean' => $this->context->typeRegistry->boolean,
					'Integer' => $this->context->typeRegistry->integer(),
					'Real' => $this->context->typeRegistry->real(),
					'String' => $this->context->typeRegistry->string(),
					default => $this->context->typeRegistry->withName(new TypeNameIdentifier($typeName)),
				}				;
				//$type = $this->context->typeRegistry->withName(new TypeNameIdentifier());
				if ($type->isSubtypeOf($targetType->refType)) {
					return $this->context->valueRegistry->type($type);
				}
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("The type should be a subtype of %s", $targetType->refType)
				);
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

	private function hydrateAtom(Value $value, AtomType $targetType, string $hydrationPath): AtomValue {
		$method = $this->methodRegistry->method(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->name)
			)
		);
		if ($method instanceof Method) {
			$result = $method->execute(
				TypedValue::forValue($value),
				TypedValue::forValue($this->context->valueRegistry->null)
			);
			$resultValue = $result->value;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("Atom hydration failed. Error: %s", $resultValue->errorValue)
				//TODO - consider a better error message
				);
			}
			return $resultValue;
		}
		return $targetType->value;
	}

	private function hydrateEnumeration(Value $value, EnumerationType $targetType, string $hydrationPath): Value {
		return $this->hydrateEnumerationSubset($value, $targetType, $hydrationPath);
	}

	private function hydrateEnumerationSubset(Value $value, EnumerationSubsetType $targetType, string $hydrationPath): EnumerationValue {
		$method = $this->methodRegistry->method(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->enumeration->name)
			)
		);
		if ($method instanceof Method) {
			$result = $method->execute(
				TypedValue::forValue($value),
				TypedValue::forValue($this->context->valueRegistry->null)
			);
			$resultValue = $result->value;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("Enumeration hydration failed. Error: %s", $resultValue->errorValue)
					//TODO - consider a better error message
				);
			}
			foreach($targetType->subsetValues as $enumValue) {
				if ($enumValue === $resultValue) {
					return $resultValue;
				}
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The enumeration value %s is not among %s",
					$resultValue,
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		if ($value instanceof StringValue) {
			foreach($targetType->subsetValues as $enumValue) {
				if ($enumValue->name->identifier === $value->literalValue) {
					return $this->context->valueRegistry->enumerationValue(
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

	/*private function hydrateEnumerationOld(Value $value, EnumerationType $targetType, string $hydrationPath): Value {
		$method = $this->methodRegistry->method(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->name)
			)
		);
		if ($method instanceof Method) {
			$result = $method->execute(
				TypedValue::forValue($value),
				TypedValue::forValue($this->context->valueRegistry->null)
			);
			$resultValue = $result->value;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("Enumeration hydration failed. Error: %s", $resultValue->errorValue)
					//TODO - consider a better error message
				);
			}
			return $resultValue;
		}
		if ($value instanceof StringValue) {
			foreach($targetType->values as $enumValue) {
				if ($enumValue->name->identifier === $value->literalValue) {
					return $this->context->valueRegistry->enumerationValue(
						$targetType->name,
						$enumValue->name
					);
				}
			}
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string with a value among %s",
				implode(', ', $targetType->values)
			)
		);
	}
	private function hydrateEnumerationSubsetOld(Value $value, EnumerationSubsetType $targetType, string $hydrationPath): EnumerationValue {
		$resultValue = $this->hydrateEnumeration($value, $targetType->enumeration, $hydrationPath);
		if (!in_array($resultValue, $targetType->subsetValues)) {
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The enumeration value %s is not among %s",
					$resultValue,
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		return $resultValue;
	}*/

	/*private function hydrateIntersection(Value $value, IntersectionType $targetType, string $hydrationPath): Value {
		$values = [];
		foreach($targetType->types() as $type) {
			$result = $this->hydrate($value, $type, $hydrationPath);
			if (!($result instanceof DictValue)) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					"A record value is expected"
				);
			}
			/** @noinspection SlowArrayOperationsInLoopInspection * /
			$values = array_merge($values, $result->values());
		}
		return $this->context->valueRegistry->dict($values);
	}*/

	private function hydrateUnion(Value $value, UnionType $targetType, string $hydrationPath): Value {
		$exceptions = [];
		foreach($targetType->types as $type) {
			try {
				return $this->hydrateValue($value, $type, $hydrationPath);
			} catch (HydrationException $ex) {
				$exceptions[] = $ex;
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		throw $exceptions[0];
	}

	private function hydrateAlias(Value $value, AliasType $targetType, string $hydrationPath): Value {
		return $this->hydrateValue($value, $targetType->aliasedType, $hydrationPath);
	}

	private function hydrateResult(Value $value, ResultType $targetType, string $hydrationPath): Value {
		try {
			return $this->hydrateValue($value, $targetType->returnType, $hydrationPath);
		} catch (HydrationException $ex) {
			try {
				return $this->context->valueRegistry->error(
					$this->hydrateValue($value, $targetType->errorType, $hydrationPath)
				);
			} catch (HydrationException) {
				throw $ex;
			}
		}
	}

	private function hydrateSubtype(Value $value, SubtypeType $targetType, string $hydrationPath): Value {
		$method = $this->methodRegistry->method(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->name)
			)
		);
		if ($method instanceof Method) {
			$result = $method->execute(
				TypedValue::forValue($value),
				TypedValue::forValue($this->context->valueRegistry->null)
			);
			$resultValue = $result->value;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("Subtype hydration failed. Error: %s", $resultValue->errorValue)
				//TODO - consider a better error message
				);
			}
			return $resultValue;
		}

		$baseValue = $this->hydrateValue($value, $targetType->baseType, $hydrationPath);

		$constructorType = $this->context->typeRegistry->atom(new TypeNameIdentifier('Constructor'));
		$validatorMethod = $this->methodRegistry->method(
			$constructorType,
			new MethodNameIdentifier('as' . $targetType->name->identifier)
		);
		if ($validatorMethod instanceof Method) {
			$result = $validatorMethod->execute(
				TypedValue::forValue($constructorType->value),
				TypedValue::forValue($baseValue),
			);
			$resultValue = $result->value;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$baseValue,
					$hydrationPath,
					sprintf('Value construction failed. Error: %s', $resultValue->errorValue)
				);
			}
		}
		return $this->context->valueRegistry->subtypeValue(
			$targetType->name,
			$baseValue
		);
	}

	private function hydrateSealed(Value $value, SealedType $targetType, string $hydrationPath): Value {
		$method = $this->methodRegistry->method(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->name)
			)
		);
		if ($method instanceof Method) {
			$result = $method->execute(
				TypedValue::forValue($value),
				TypedValue::forValue($this->context->valueRegistry->null)
			);
			$resultValue = $result->value;
			if ($resultValue instanceof ErrorValue) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("Sealed type hydration failed. Error: %s", $resultValue->errorValue)
				//TODO - consider a better error message
				);
			}
			return $resultValue;
		}
		$baseValue = $this->hydrateValue($value, $targetType->valueType, $hydrationPath);
		if ($baseValue instanceof RecordValue) {
			$constructorType = $this->context->typeRegistry->atom(new TypeNameIdentifier('Constructor'));
			$validatorMethod = $this->methodRegistry->method(
				$constructorType,
				new MethodNameIdentifier('as' . $targetType->name->identifier)
			);
			if ($validatorMethod instanceof Method) {
				$result = $validatorMethod->execute(
					TypedValue::forValue($constructorType->value),
					TypedValue::forValue($baseValue),
				);
				$resultValue = $result->value;
				if ($resultValue instanceof ErrorValue) {
					throw new HydrationException(
						$baseValue,
						$hydrationPath,
						sprintf('Value construction failed. Error: %s', $resultValue->errorValue)
					);
				}
			}
			return $this->context->valueRegistry->sealedValue(
				$targetType->name,
				$baseValue
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be a record"
		);
	}

	private function hydrateMutable(Value $value, MutableType $targetType, string $hydrationPath): MutableValue {
		return $this->context->valueRegistry->mutable(
			$targetType->valueType,
			$this->hydrateValue($value, $targetType->valueType, $hydrationPath)
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
		while($value instanceof SubtypeValue) {
			$value = $value->baseValue;
		}
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
		if ($value instanceof TupleValue) {
			$l = count($value->values);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				$refType = $targetType->itemType;
				$result = [];
				foreach($value->values as $seq => $item) {
					$result[] = $this->hydrateValue($item, $refType, "{$hydrationPath}[$seq]");
				}
				return $this->context->valueRegistry->tuple($result);
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
					$result[] = $this->hydrateValue($item, $refType, "{$hydrationPath}[$seq]");
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
					$result[] = $this->hydrateValue($val,
						$targetType->types[$seq] ?? $targetType->restType, "{$hydrationPath}[$seq]");
				}
			}
			return $this->context->valueRegistry->tuple($result);

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
					$result[$key] = $this->hydrateValue($item, $refType, "$hydrationPath.$key");
				}
				return $this->context->valueRegistry->record($result);
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
					$result[$key] = $this->hydrateValue($item, $refType, "$hydrationPath.$key");
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
					$result[$key] = $this->hydrateValue($val, $targetType->restType, "$hydrationPath.$key");
				}
			}
			return $this->context->valueRegistry->record( $result);
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
				return $this->context->valueRegistry->real((float)(string)$value->literalValue);
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