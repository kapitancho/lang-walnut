<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class ItemHelper {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validateDataOpenType(
		DataType|OpenType $targetType,
		Type $parameterType,
		Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		$valueType = $this->toBaseType($targetType->valueType);
		if ($valueType instanceof TupleType || $valueType instanceof ArrayType) {
			return $this->validateArrayItem($valueType, $parameterType, $origin);
		}
		if ($valueType instanceof RecordType || $valueType instanceof MapType) {
			return $this->validateMapItem($valueType, $parameterType, $origin);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	public function validateArrayItem(
		ArrayType|TupleType $targetType,
		Type $parameterType,
		Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		if ($parameterType instanceof IntegerType) {
			$arrayType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$returnType = $arrayType->itemType;
			if ($targetType instanceof TupleType) {
				$min = $parameterType->numberRange->min;
				$max = $parameterType->numberRange->max;
				if ($min !== MinusInfinity::value && $min->value >= 0) {
					if ($parameterType instanceof IntegerSubsetType) {
						$returnType = $this->typeRegistry->union(
							array_map(
								static fn(Number $value) =>
									$targetType->types[(string)$value] ?? $targetType->restType,
								$parameterType->subsetValues
							)
						);
					} else {
						$isWithinLimit = $max !== PlusInfinity::value && $max->value < count($targetType->types);
						$returnType = $this->typeRegistry->union(
							$isWithinLimit ?
							array_slice($targetType->types, (int)(string)$min->value, (int)(string)$max->value - (int)(string)$min->value + 1) :
							[... array_slice($targetType->types, (int)(string)$min->value), $targetType->restType]
						);
					}
				}
			}

			return $this->validationFactory->validationSuccess(
				$parameterType->numberRange->max !== PlusInfinity::value &&
				$arrayType->range->minLength > $parameterType->numberRange->max->value ?
					$returnType :
					$this->typeRegistry->result(
						$returnType,
						$this->typeRegistry->typeByName(new TypeName('IndexOutOfRange'))
					)
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
			$origin
		);
	}

	public function validateMapItem(
		IntersectionType|MapType|RecordType|MetaType|AliasType $targetType,
		Type $parameterType,
		Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);

		if ($targetType instanceof IntersectionType) {
			//TODO: this is not a long-term fix.
			/** @var RecordType[] $intersectionTypes */
			$intersectionTypes = $targetType->types;
			$types = array_map(
				fn(MapType|RecordType|MetaType|AliasType $type) =>
					$this->validateMapItem($type, $parameterType, $origin),
				$intersectionTypes
			);
			// TODO: handle validation failures
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->intersection($types)
			);
		}
		$mapType = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($targetType instanceof MetaType && $targetType->value === MetaTypeValue::Record) {
			$mapType = $this->typeRegistry->map();
		}
		//$mapItemNotFound = $this->typeRegistry->core->mapItemNotFound;
		$mapItemNotFound = $this->typeRegistry->typeByName(new TypeName('MapItemNotFound'));

		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof StringType) {
			$returnType = $mapType->itemType;
			if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
				$tConv = static fn(Type $fType): Type => $fType instanceof OptionalKeyType ?
					$this->typeRegistry->result($fType->valueType, $mapItemNotFound) :
					$fType;
				$returnType = $this->typeRegistry->union(
					array_map(
						static fn(string $value) => $tConv(
							$targetType->types[$value] ??
							$targetType->restType
						),
						$parameterType->subsetValues
					)
				);
				$allKeys = array_filter($parameterType->subsetValues,
					static fn(string $value) => array_key_exists($value, $targetType->types)
				);
				if (count($allKeys) === count($parameterType->subsetValues)) {
					return $this->validationFactory->validationSuccess($returnType);
				}
			}
			/*if ($returnType instanceof NothingType) {
				throw new AnalyserException(sprintf("[%s] No property exists that matches the type: %s", __CLASS__, $parameterType));
			}*/
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->result($returnType, $mapItemNotFound)
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
			$origin
		);
	}

	/** @throws ExecutionException */
	public function executeDataOpenType(
		DataValue|OpenValue $target,
		Value $parameter
	): Value {
		$baseValue = $target->value;
		if ($baseValue instanceof TupleValue) {
			return $this->executeArrayItem($baseValue, $parameter);
		}
		if ($baseValue instanceof RecordValue) {
			return $this->executeMapItem($baseValue, $parameter);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

	/** @throws ExecutionException */
	public function executeArrayItem(
		TupleValue $target,
		Value $parameter
	): Value {
		if ($parameter instanceof IntegerValue) {
			$values = $target->values;
			return $values[(int)(string)$parameter->literalValue] ?? $this->valueRegistry->error(
				$this->valueRegistry->data(new TypeName('IndexOutOfRange'),
					$this->valueRegistry->record(['index' => $parameter])
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

	/** @throws ExecutionException */
	public function executeMapItem(
		RecordValue $target,
		Value $parameter
	): Value {
		if ($parameter instanceof StringValue) {
			$values = $target->values;
			return $values[$parameter->literalValue] ?? $this->valueRegistry->error(
				$this->valueRegistry->core->mapItemNotFound(
					$this->valueRegistry->record(['key' => $parameter])
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}