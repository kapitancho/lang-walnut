<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;

final readonly class CustomTypeHydrator {
	public function __construct(
		private Hydrator      $hydrator,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}


	public function hydrateAtom(Value $value, AtomType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Atom hydration failed. Error: %s"
		) ?? $targetType->value;
	}

	public function hydrateEnumeration(Value $value, EnumerationType $targetType, string $hydrationPath): Value {
		return $this->hydrateEnumerationSubset($value, $targetType, $hydrationPath);
	}

	public function hydrateEnumerationSubset(Value $value, EnumerationSubsetType $targetType, string $hydrationPath): EnumerationValue {
		$result = $this->tryJsonValueCast(
			$targetType->enumeration, $value, $hydrationPath,
			"Enumeration hydration failed. Error: %s"
		);
		if ($result) {
			foreach($targetType->subsetValues as $enumValue) {
				if ($enumValue === $result) {
					/** @phpstan-ignore-next-line return.type */
					return $enumValue;
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
					return $this->valueRegistry->enumerationValue(
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

	public function hydrateData(Value $value, DataType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Data type hydration failed. Error: %s"
		) ?? $this->valueRegistry->dataValue(
			$targetType->name,
			$this->hydrator->hydrate($value, $targetType->valueType, $hydrationPath)
		);
	}

	public function hydrateOpen(Value $value, OpenType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Open type hydration failed. Error: %s"
		) ?? $this->constructValue(
			$targetType,
			$this->hydrator->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	public function hydrateSealed(Value $value, SealedType $targetType, string $hydrationPath): Value {
		return $this->tryJsonValueCast(
			$targetType, $value, $hydrationPath,
			"Sealed type hydration failed. Error: %s"
		) ?? $this->constructValue(
			$targetType,
			$this->hydrator->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	public function tryJsonValueCast(
		NamedType $targetType,
		Value $value,
		string $hydrationPath,
		string $hydrationErrorMessage
	): Value|null {
		$result = $this->methodContext->safeExecuteMethod(
			$value,
			new MethodNameIdentifier(
				sprintf('as%s', $targetType->name)
			),
			$this->valueRegistry->null
		);
		if ($result !== UnknownMethod::value) {
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

	public function constructValue(Type $targetType, Value $baseValue, string $hydrationPath): Value {
		$result = new ValueConstructor()->executeValidator(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext,
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

}