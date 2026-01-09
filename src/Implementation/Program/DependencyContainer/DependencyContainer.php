<?php

namespace Walnut\Lang\Implementation\Program\DependencyContainer;

use SplObjectStorage;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;

final class DependencyContainer implements DependencyContainerInterface {

	/** @var array<Type, Value|DependencyError> $cache */
	private array $cache;
	/** @var SplObjectStorage<Type, Type> $visited */
	private SplObjectStorage $visited;

	public function __construct(
		private readonly ProgramRegistry   $programRegistry,
		private readonly ValueRegistry     $valueRegistry,
		private readonly MethodFinder      $methodFinder,
		private readonly ValueConstructor  $valueConstructor,
	) {
		$this->cache = [];
		$this->visited = new SplObjectStorage;
	}

	private function findValueByNamedType(NamedType $type): Value|DependencyError {
		try {
			$sType = $this->valueRegistry->type($type);
			$dependencyContainer = $this->valueRegistry->atom(new TypeNameIdentifier('DependencyContainer'));
			$method = $this->methodFinder->methodForType($dependencyContainer->type,
				new MethodNameIdentifier('as'));

			$result = $method->execute($this->programRegistry, $dependencyContainer, $sType);

			if ($result instanceof ErrorValue && $result->errorValue instanceof DataValue &&
				$result->errorValue->type->name->equals(new TypeNameIdentifier('CastNotAvailable'))
			) {
				if ($type instanceof AliasType) {
					return $this->attemptToFindAlias($type);
				}
				return new DependencyError(UnresolvableDependency::notFound, $type);
			}
			return $result;
            // @codeCoverageIgnoreStart
		} catch (AnalyserException) {
			return new DependencyError(UnresolvableDependency::notFound, $type);
		}
		// @codeCoverageIgnoreEnd
	}

	private function attemptToFindAlias(AliasType $aliasType): Value|DependencyError {
		$baseType = $aliasType->aliasedType;
		return $this->findValueByType($baseType);
	}

	private function findTupleValue(TupleType $tupleType): Value|DependencyError {
		$found = [];
		foreach($tupleType->types as $index => $type) {
			$foundValue = $this->valueByType($type);
			if ($foundValue instanceof DependencyError) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$type,
					sprintf("Error while creating value for field %d", $index)
				);
			}
			$found[$index] = $foundValue;
		}
		return $this->valueRegistry->tuple($found);
	}

	private function findRecordValue(RecordType $recordType): Value|DependencyError {
		$found = [];
		foreach($recordType->types as $key => $field) {
			$foundValue = $this->valueByType($field);

			if ($foundValue instanceof DependencyError) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$field,
					sprintf("Error while creating value for field %s", $key)
				);
			}
			$found[$key] = $foundValue;
		}
		return $this->valueRegistry->record($found);
	}

	private function findDataValue(DataType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$baseValue = $this->findValueByType($type->valueType);
			if ($baseValue instanceof Value) {
				return $this->valueRegistry->dataValue(
					$type->name,
					$baseValue
				);
			}
		}
		return $found;
	}

	private function findSealedOrOpenType(SealedType|OpenType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$constructor = $this->valueRegistry->atom(new TypeNameIdentifier('Constructor'));
			$method = $this->methodFinder->methodForType($constructor->type,
				new MethodNameIdentifier($type->name->identifier));
			if ($method instanceof CustomMethod) {
				$baseValue = $this->findValueByType($method->parameterType);
			} else {
				$baseValue = $this->findValueByType($type->valueType);
			}
			if ($baseValue instanceof Value) {
				$result = $this->valueConstructor->executeConstructor(
					$this->programRegistry,
					$type,
					$baseValue
				);
				if ($result instanceof ErrorValue) {
					return new DependencyError(
						UnresolvableDependency::errorWhileCreatingValue,
						$type,
						sprintf("Error while creating value for %s type %s",
							match(true) {
								$type instanceof SealedType => 'sealed',
								$type instanceof OpenType => 'open',
							},
							$type)
					);
				}
				return $result;
			}
			return $baseValue;
		}
		return $found;
	}

	private function findValueByType(Type $type): Value|DependencyError {
		return match(true) {
			$type instanceof AtomType => $type->value,
            $type instanceof DataType => $this->findDataValue($type),
            $type instanceof OpenType, $type instanceof SealedType => $this->findSealedOrOpenType($type),
			$type instanceof NamedType => $this->findValueByNamedType($type),
			$type instanceof TupleType => $this->findTupleValue($type),
			$type instanceof RecordType => $this->findRecordValue($type),
			default => new DependencyError(UnresolvableDependency::unsupportedType, $type)
		};
	}

	public function valueByType(Type $type): Value|DependencyError {
		if ($this->visited->contains($type)) {
			return new DependencyError(UnresolvableDependency::circularDependency, $type);
		}
		$typeStr = (string)$type;
		$cached = $this->cache[$typeStr] ?? null;
		if ($cached) {
			return $cached;
		}
		$this->visited->attach($type);
		$result = $this->findValueByType($type);
		if (!($result instanceof DependencyError) && !$result->type->isSubtypeOf($type)) {
			$result = new DependencyError(
				UnresolvableDependency::errorWhileCreatingValue,
				$type,
				sprintf("The value %s is not a subtype of %s", $result->type, $type)
			);
		}
		$this->cache[$typeStr] = $result;
		$this->visited->detach($type);
		return $result;
	}
}