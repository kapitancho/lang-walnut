<?php

namespace Walnut\Lang\Implementation\Program\DependencyContainer;

use SplObjectStorage;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
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

final class DependencyContainer implements DependencyContainerInterface {

	/** @var array<Type, Value|DependencyError> */
	private array $cache;
	/** @var SplObjectStorage<Type> */
	private SplObjectStorage $visited;
	private readonly MethodCallExpression $containerCastExpression;

	public function __construct(
		private readonly ProgramRegistry $programRegistry,
		private readonly ExecutionContext $globalContext,
		private readonly MethodRegistry $methodRegistry,
		private readonly ExpressionRegistry $expressionRegistry,
	) {
		$this->cache = [];
		$this->visited = new SplObjectStorage;
	}

	private function containerCastExpression(): MethodCallExpression {
		return $this->containerCastExpression ??= $this->expressionRegistry->methodCall(
			$this->expressionRegistry->constant(
				$this->programRegistry->valueRegistry->atom(new TypeNameIdentifier('DependencyContainer'))
			),
			new MethodNameIdentifier('as'),
			$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
		);
	}

	private function findValueByNamedType(NamedType $type): Value|DependencyError {
		try {
			$sType = $this->programRegistry->valueRegistry->type($type);
			$containerCastExpression = $this->containerCastExpression();
			$containerCastExpression->analyse(
				$this->programRegistry->analyserContext->withAddedVariableType(
					new VariableNameIdentifier('#'),
					$sType->type
				)
			);
			$result = $containerCastExpression->execute(
				$this->programRegistry->executionContext->withAddedVariableValue(
					new VariableNameIdentifier('#'),
					$sType
				)
			);
			if ($result->value instanceof ErrorValue && $result->value->errorValue instanceof DataValue &&
				$result->value->errorValue->type->name->equals(new TypeNameIdentifier('CastNotAvailable'))
			) {
				if ($type instanceof AliasType) {
					return $this->attemptToFindAlias($type);
				}
				return new DependencyError(UnresolvableDependency::notFound, $type);
			}
			return $result->value;
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
					$foundValue->type,
					sprintf("Error while creating value for field %d", $index)
				);
			}
			/*if ($foundValue instanceof ErrorValue &&
				($err = $foundValue->errorValue) instanceof SealedValue &&
				$err->type->name->equals(new TypeNameIdentifier('DependencyContainerError'))
			) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$err->value->values['errorOnType']->typeValue(),
					sprintf("Nested DependencyContainerError while creating value for field %d", $index)
				);
			}*/
			$found[$index] = $foundValue;
		}

		return ($this->programRegistry->valueRegistry->tuple($found));
	}

	private function findRecordValue(RecordType $recordType): Value|DependencyError {
		$found = [];
		foreach($recordType->types as $key => $field) {
			$foundValue = $this->valueByType($field);

			if ($foundValue instanceof DependencyError) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$foundValue->type,
					sprintf("Error while creating value for field %s", $key)
				);
			}
			//TODO: improve
			/*if ($foundValue instanceof ErrorValue &&
				($err = $foundValue->errorValue) instanceof SealedValue &&
				$err->type->name->equals(new TypeNameIdentifier('DependencyContainerError'))
			) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$err->value->values['errorOnType']->typeValue(),
					sprintf("Nested DependencyContainerError while creating value for field %s", $key)
				);
			}*/
			$found[$key] = $foundValue;
		}
		return ($this->programRegistry->valueRegistry->record($found));
	}

	private function findDataValue(DataType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$baseValue = $this->findValueByType($type->valueType);
			if ($baseValue instanceof Value) {
				return $this->programRegistry->valueRegistry->dataValue(
					$type->name,
					$baseValue
				);
			}
		}
		return $found;
	}

	private function findOpenValue(OpenType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$constructor = $this->programRegistry->valueRegistry->atom(new TypeNameIdentifier('Constructor'));
			$method = $this->methodRegistry->methodForType($constructor->type,
				new MethodNameIdentifier($type->name->identifier));
			if ($method instanceof CustomMethod) {
                $baseValue = $this->findValueByType($method->parameterType);
			} else {
				$baseValue = $this->findValueByType($type->valueType);
			}
			if ($baseValue instanceof Value) {
				$result = $this->expressionRegistry->methodCall(
					$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
					new MethodNameIdentifier('construct'),
					$this->expressionRegistry->constant(
						$this->programRegistry->valueRegistry->type($type)
					)
				)->execute(
					$this->globalContext->withAddedVariableValue(
						new VariableNameIdentifier('#'),
						$baseValue
					)
				);
				if ($result->value instanceof ErrorValue) {
					return new DependencyError(
						UnresolvableDependency::errorWhileCreatingValue,
						$type,
						sprintf("Error while creating value for open type %s", $type)
					);
				}
				return $result->value;
			}
		}
		return $found;
	}

	private function findSealedValue(SealedType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$constructor = $this->programRegistry->valueRegistry->atom(new TypeNameIdentifier('Constructor'));
			$method = $this->methodRegistry->methodForType($constructor->type,
				new MethodNameIdentifier($type->name->identifier));
			if ($method instanceof CustomMethod) {
                $baseValue = $this->findValueByType($method->parameterType);
			} else {
				$baseValue = $this->findValueByType($type->valueType);
			}
			if ($baseValue instanceof Value) {
				$result = $this->expressionRegistry->methodCall(
					$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
					new MethodNameIdentifier('construct'),
					$this->expressionRegistry->constant(
						$this->programRegistry->valueRegistry->type($type)
					)
				)->execute(
					$this->globalContext->withAddedVariableValue(
						new VariableNameIdentifier('#'),
						$baseValue
					)
				);
				if ($result->value instanceof ErrorValue) {
					return new DependencyError(
						UnresolvableDependency::errorWhileCreatingValue,
						$type,
						sprintf("Error while creating value for sealed type %s", $type)
					);
				}
				return $result->value;
			}
		}
		return $found;
	}

	private function findValueByType(Type $type): Value|DependencyError {
		return match(true) {
			$type instanceof AtomType => ($type->value),
            $type instanceof DataType => $this->findDataValue($type),
            $type instanceof OpenType => $this->findOpenValue($type),
			$type instanceof SealedType => $this->findSealedValue($type),
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