<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer;

use SplObjectStorage;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyError as DependencyErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\DependencyContainer\OpenType;
use Walnut\Lang\Almond\Engine\Implementation\DependencyContainer\SealedType;
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
			return new DependencyError('circularDependency', $type);
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
				'errorWhileCreatingValue',
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
			default => new DependencyError('unsupportedType', $type)
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

	private function attemptToFindAlias(AliasType $aliasType): Value|DependencyError {
		$baseType = $aliasType->aliasedType;
		return $this->findValueByType($baseType);
	}

	private function findValueByNamedType(NamedType $type): Value|DependencyErrorInterface {
		$sType = $this->valueRegistry->type($type);
		$dependencyContainerType = $this->typeRegistry
			->typeByName(new TypeName('DependencyContainer'));

		$validationResult = $this->methodContext->validateCast(
			$dependencyContainerType,
			$type->name,
			null
		);
		if ($validationResult instanceof ValidationFailure) {
			return new DependencyError('not found', $type);
		}
		$result = $this->methodContext->executeCast($dependencyContainerType->value, $type->name);
		if (
			$result instanceof ErrorValue &&
			$result->errorValue instanceof DataValue &&
			$result->errorValue->type->name->equals(new TypeName('CastNotAvailable'))
		) {
			if ($type instanceof AliasType) {
				return $this->attemptToFindAlias($type);
			}
			return new DependencyError('not found', $type);
		}
		return $result;
	}

}