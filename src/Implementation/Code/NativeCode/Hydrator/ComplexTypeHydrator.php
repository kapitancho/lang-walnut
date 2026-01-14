<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\ComplexTypeHydrator as ComplexTypeHydratorInterface;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\HydrationException;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\Hydrator;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\ComplexType;
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
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;

final readonly class ComplexTypeHydrator implements ComplexTypeHydratorInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}


	/** @throws HydrationException */
	public function hydrate(Hydrator $hydrator, Value $value, ComplexType $targetType, string $hydrationPath): Value {
		if ($targetType instanceof NamedType) {
			$namedValue = $this->tryHydrateNamed($targetType, $value, $hydrationPath);
			if ($namedValue !== null) {
				return $namedValue;
			}
		}
		/** @phpstan-ignore-next-line var.type */
		$fn = match(true) {
			$targetType instanceof AliasType => $this->hydrateAlias(...),
			$targetType instanceof AtomType => $this->hydrateAtom(...),
			$targetType instanceof EnumerationType => $this->hydrateEnumeration(...),
			$targetType instanceof EnumerationSubsetType => $this->hydrateEnumerationSubset(...),
			$targetType instanceof DataType => $this->hydrateData(...),
			$targetType instanceof OpenType => $this->hydrateOpen(...),
			$targetType instanceof SealedType => $this->hydrateSealed(...),
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

	private function hydrateAlias(Hydrator $hydrator, Value $value, AliasType $targetType, string $hydrationPath): Value {
		return $hydrator->hydrate($value, $targetType->aliasedType, $hydrationPath);
	}

	private function hydrateAtom(Hydrator $hydrator, Value $value, AtomType $targetType, string $hydrationPath): Value {
		return $targetType->value;
	}

	private function hydrateEnumerationFromString(Value $value, EnumerationSubsetType $targetType, string $hydrationPath): EnumerationValue {
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

	private function hydrateEnumeration(Hydrator $hydrator, Value $value, EnumerationType $targetType, string $hydrationPath): Value {
		return $this->hydrateEnumerationFromString($value, $targetType, $hydrationPath);
	}

	private function hydrateEnumerationSubset(Hydrator $hydrator, Value $value, EnumerationSubsetType $targetType, string $hydrationPath): EnumerationValue {
		$result = $this->tryHydrateNamed($targetType->enumeration, $value, $hydrationPath);
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
		return $this->hydrateEnumerationFromString($value, $targetType, $hydrationPath);
	}

	private function hydrateData(Hydrator $hydrator, Value $value, DataType $targetType, string $hydrationPath): Value {
		return $this->valueRegistry->dataValue(
			$targetType->name,
			$hydrator->hydrate($value, $targetType->valueType, $hydrationPath)
		);
	}

	private function hydrateOpen(Hydrator $hydrator, Value $value, OpenType $targetType, string $hydrationPath): Value {
		return $this->constructValue(
			$targetType,
			$hydrator->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	private function hydrateSealed(Hydrator $hydrator, Value $value, SealedType $targetType, string $hydrationPath): Value {
		return $this->constructValue(
			$targetType,
			$hydrator->hydrate($value, $targetType->valueType, $hydrationPath),
			$hydrationPath
		);
	}

	private function tryHydrateNamed(
		NamedType $targetType,
		Value $value,
		string $hydrationPath
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
					sprintf(
						"Type %s hydration failed. Error: %s",
						$targetType->name,
						$resultValue->errorValue
					)
				);
			}
			return $resultValue;
		}
		return null;
	}

	private function constructValue(Type $targetType, Value $baseValue, string $hydrationPath): Value {
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