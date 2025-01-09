<?php

namespace Walnut\Lang\Implementation\Program\DependencyContainer;

use SplObjectStorage;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
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
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;

final class DependencyContainer implements DependencyContainerInterface {

	/** @var SplObjectStorage<Type, Value|DependencyError> */
	private SplObjectStorage $cache;
	/** @var SplObjectStorage<Type> */
	private SplObjectStorage $visited;
	private readonly MethodCallExpression $containerCastExpression;

	public function __construct(
		private readonly ProgramRegistry $programRegistry,
		private readonly ExecutionContext $globalContext,
		private readonly MethodRegistry $methodRegistry,
		private readonly ExpressionRegistry $expressionRegistry,
	) {
		$this->cache = new SplObjectStorage;
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
			$sType = TypedValue::forValue($this->programRegistry->valueRegistry->type($type));
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
			)->value;
			if ($result instanceof ErrorValue && $result->errorValue instanceof SealedValue &&
				$result->errorValue->type->name->equals(new TypeNameIdentifier('CastNotAvailable'))
			) {
				if ($type instanceof AliasType) {
					return $this->attemptToFindAlias($type);
				}
				return new DependencyError(UnresolvableDependency::notFound, $type);
			}
			return $result;
		} catch (AnalyserException) {
			return new DependencyError(UnresolvableDependency::notFound, $type);
		}
	}

	private function attemptToFindAlias(AliasType $aliasType): Value|DependencyError {
		$baseType = $aliasType->aliasedType;
		return $this->findValueByType($baseType);
	}

	private function findTupleValue(TupleType $tupleType): Value|DependencyError {
		$found = [];
		foreach($tupleType->types as $type) {
			$foundValue = $this->valueByType($type);
			if ($foundValue instanceof DependencyError) {
				return $foundValue;
			}
			$found[] = $foundValue;
		}
		return $this->programRegistry->valueRegistry->tuple($found);
	}

	private function findRecordValue(RecordType $recordType): Value|DependencyError {
		$found = [];
		foreach($recordType->types as $key => $field) {
			$foundValue = $this->valueByType($field);

			if ($foundValue instanceof DependencyError) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$foundValue->type
				);
			}
			//TODO: improve
			if ($foundValue instanceof ErrorValue &&
				($err = $foundValue->errorValue) instanceof SealedValue &&
				$err->type->name->equals(new TypeNameIdentifier('DependencyContainerError'))
			) {
				return new DependencyError(
					UnresolvableDependency::errorWhileCreatingValue,
					$err->value->values()['errorOnType']->typeValue()
				);
			}
			if ($foundValue instanceof DependencyError) {
				return $foundValue;
			}
			$found[$key] = $foundValue;
		}
		return $this->programRegistry->valueRegistry->record($found);
	}

	private function findSubtypeValue(SubtypeType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$baseValue = $this->findValueByType($type->baseType);
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
						TypedValue::forValue($baseValue)
					)
				)->value;
				if ($result instanceof ErrorValue) {
					return new DependencyError(UnresolvableDependency::errorWhileCreatingValue, $type);
				}
				return $result;
			}
		}
		return $found;
	}

	private function findSealedValue(SealedType $type): Value|DependencyError {
		$found = $this->findValueByNamedType($type);
		if ($found instanceof DependencyError) {
			$constructor = $this->programRegistry->valueRegistry->atom(new TypeNameIdentifier('Constructor'));
			$method = $this->methodRegistry->method($constructor->type,
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
						TypedValue::forValue($baseValue)
					)
				)->value;
				if ($result instanceof ErrorValue) {
					return new DependencyError(UnresolvableDependency::errorWhileCreatingValue, $type);
				}
				return $result;
			}
		}
		return $found;
	}

	private function findValueByType(Type $type): Value|DependencyError {
		return match(true) {
			$type instanceof AtomType => $type->value,
            $type instanceof SubtypeType => $this->findSubtypeValue($type),
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
		$cached = $this->cache[$type] ?? null;
		if ($cached) {
			return $cached;
		}
		$this->visited->attach($type);
		$result = $this->findValueByType($type);
		if (!($result instanceof DependencyError) && !$result->type->isSubtypeOf($type)) {
			$result = new DependencyError(UnresolvableDependency::errorWhileCreatingValue, $type);
		}
		$this->cache[$type] = $result;
		$this->visited->detach($type);
		return $result;
	}
}