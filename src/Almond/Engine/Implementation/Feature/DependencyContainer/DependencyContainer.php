<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer;

use SplObjectStorage;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainerErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyError as DependencyErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\ValidationError;

final class DependencyContainer implements DependencyContainerInterface {

	/** @var array<Type, Value|DependencyError> $cache */
	private array $cache;
	/** @var SplObjectStorage<Type, Type> $visited */
	private SplObjectStorage $visited;

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly ValueRegistry $valueRegistry,
		private readonly MethodContext $methodContext
	) {
		$this->cache = [];
		$this->visited = new SplObjectStorage;
	}

	public function checkForType(Type $type, UserlandFunction $origin): ValidationErrorInterface|null {
		$value = $this->valueForType($type);
		return $value instanceof DependencyErrorInterface ? new ValidationError(
			ValidationErrorType::dependencyNotFound,
			sprintf(
				'No implementation found for the requested type "%s".',
				$type
			),
			$origin
		) : null;
	}

	public function valueForType(Type $type): Value|DependencyErrorInterface {
		if ($this->visited->offsetExists($type)) {
			return new DependencyError(
				DependencyContainerErrorType::circularDependency,
				$type
			);
		}
		$typeStr = (string)$type;
		$cached = $this->cache[$typeStr] ?? null;
		if ($cached) {
			return $cached;
		}
		$this->visited->offsetSet($type);
		$result = $this->findValueByType($type);
		if (!($result instanceof DependencyError) && !$result->type->isSubtypeOf($type)) {
			$result = new DependencyError(
				DependencyContainerErrorType::errorWhileCreatingValue,
				$type,
				sprintf("The value %s is not a subtype of %s", $result->type, $type)
			);
		}
		$this->cache[$typeStr] = $result;
		$this->visited->offsetUnset($type);
		return $result;

	}

	private function findValueByType(Type $type): Value|DependencyError {
		return match(true) {
			$type instanceof AtomType => $type->value,
			$type instanceof DataType => $this->findDataValue($type),
			$type instanceof OpenType, $type instanceof SealedType => $this->findSealedOrOpenType($type),
			$type instanceof NamedType => $this->findValueByNamedType($type),
			$type instanceof TupleType => $this->findTupleValue($type),
			$type instanceof RecordType => $this->findRecordValue($type),
			default => new DependencyError(
				DependencyContainerErrorType::unsupportedType,
				$type
			)
		};
	}

	private function findDataValue(DataType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$baseValue = $this->findValueByType($type->valueType);
			if ($baseValue instanceof Value) {
				return $this->valueRegistry->data(
					$type->name,
					$baseValue
				);
			}
		}
		return $found;
	}

	private function findTupleValue(TupleType $tupleType): Value|DependencyError {
		$found = [];
		foreach($tupleType->types as $index => $type) {
			$foundValue = $this->valueForType($type);
			if ($foundValue instanceof DependencyErrorInterface) {
				return new DependencyError(
					DependencyContainerErrorType::errorWhileCreatingValue,
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
			$foundValue = $this->valueForType($field);

			if ($foundValue instanceof DependencyError) {
				return new DependencyError(
					DependencyContainerErrorType::errorWhileCreatingValue,
					$field,
					sprintf("Error while creating value for field %s", $key)
				);
			}
			$found[$key] = $foundValue;
		}
		return $this->valueRegistry->record($found);
	}

	private function findSealedOrOpenType(SealedType|OpenType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$constructor = $this->valueRegistry->core->constructor;
			$method = $this->methodContext->methodForValue(
				$constructor,
				new MethodName($type->name->identifier)
			);
			$baseValueType = $method instanceof UserlandMethod ?
				$method->parameterType :
				$type->valueType;
			$baseValue = $this->findValueByType($baseValueType);
			if ($baseValue instanceof Value) {
				$result = $this->methodContext->executeMethod(
					$baseValue,
					new MethodName('Construct'),
					$this->valueRegistry->type($type)
				);
				if ($result instanceof ErrorValue) {
					return new DependencyError(
						DependencyContainerErrorType::errorWhileCreatingValue,
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
	private function attemptToFindAlias(AliasType $aliasType): Value|DependencyError {
		$baseType = $aliasType->aliasedType;
		return $this->findValueByType($baseType);
	}

	private function findValueByNamedType(NamedType $type): Value|DependencyErrorInterface {
		$sType = $this->valueRegistry->type($type);
		$dependencyContainerType = $this->typeRegistry->core->dependencyContainer;

		$validationResult = $this->methodContext->validateCast(
			$dependencyContainerType,
			$type->name,
			null
		);
		if ($validationResult instanceof ValidationFailure) {
			if ($type instanceof AliasType) {
				return $this->attemptToFindAlias($type);
			}
			return new DependencyError(
				DependencyContainerErrorType::notFound,
				$type,
				implode("\n", array_map(
					fn(ValidationErrorInterface $error) => $error->message,
					$validationResult->errors
				))
			);
		}
		try {
			$result = $this->methodContext->executeCast($dependencyContainerType->value, $type->name);
		} catch (ExecutionException) {
			return new DependencyError(DependencyContainerErrorType::errorWhileCreatingValue, $type);
		}
		if (
			$result instanceof ErrorValue &&
			$result->errorValue instanceof DataValue &&
			$result->errorValue->type->name->equals(CoreType::CastNotAvailable->typeName())
		) {
			if ($type instanceof AliasType) {
				return $this->attemptToFindAlias($type);
			}
			return new DependencyError(
				DependencyContainerErrorType::notFound,
				$type
			);
		}
		return $result;
	}

}