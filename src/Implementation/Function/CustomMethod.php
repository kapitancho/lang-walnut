<?php

namespace Walnut\Lang\Implementation\Function;

use ArrayObject;
use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class CustomMethod implements CustomMethodInterface, JsonSerializable {
	use TupleAsRecord;

	private ArrayObject $isAnalysed;

	public function __construct(
		private MethodExecutionContext $methodExecutionContext,
		private DependencyContainer $dependencyContainer,
		public Type $targetType,
		public MethodNameIdentifier $methodName,
		public Type $parameterType,
		public Type $dependencyType,
		public Type $returnType,
		public FunctionBody $functionBody,
	) {
		$this->isAnalysed = new ArrayObject();
	}

	/** @throws AnalyserException */
	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		$k = sprintf("%s:%s", $targetType, $parameterType);
		if ($this->isAnalysed[$k] ?? false) {
			return $this->returnType;
		}
		$this->isAnalysed[$k] ??= true;
		if (!($parameterType->isSubtypeOf($this->parameterType) || (
			$this->parameterType instanceof RecordType &&
			$parameterType instanceof TupleType &&
			$this->isTupleCompatibleToRecord(
				$this->methodExecutionContext->typeRegistry,
				$parameterType,
				$this->parameterType
			)
		))) {
			unset($this->isAnalysed[$k]);
			throw new AnalyserException(
				sprintf(
					"Invalid parameter type: %s should be a subtype of %s",
					$parameterType,
					$this->parameterType
				)
			);
		}
		$returnType = $this->functionBody->analyse(
			$this->methodExecutionContext->globalContext,
			$this->targetType,
			$this->parameterType,
			$this->dependencyType
		);
		if (!$returnType->isSubtypeOf($this->returnType)) {
			unset($this->isAnalysed[$k]);
			throw new AnalyserException(
				sprintf(
					"Invalid return type: %s should be a subtype of %s", //TODO - add more info
					$returnType,
					$this->returnType
				)
			);
		}
		unset($this->isAnalysed[$k]);
		return $this->returnType;
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		if ($parameterValue instanceof TupleValue &&
			$this->parameterType instanceof RecordType &&
			$this->isTupleCompatibleToRecord(
				$this->methodExecutionContext->typeRegistry,
				$parameterValue->type,
				$this->parameterType
			)
		) {
			$parameterValue = $this->getTupleAsRecord(
				$this->methodExecutionContext->valueRegistry,
				$parameterValue,
				$this->parameterType
			);
		}
		$dep = null;
		if (!($this->dependencyType instanceof NothingType)) {
			$dep = $this->dependencyContainer->valueByType($this->dependencyType);
			if ($dep instanceof DependencyError) {
				return TypedValue::forValue($this->methodExecutionContext->valueRegistry->error(
					$this->methodExecutionContext->valueRegistry->sealedValue(
						new TypeNameIdentifier('DependencyContainerError'),
						$this->methodExecutionContext->valueRegistry->record([
							'targetType' => $this->methodExecutionContext->valueRegistry->type($this->dependencyType),
							'errorOnType' => $this->methodExecutionContext->valueRegistry->type($dep->type),
							'errorMessage' => $this->methodExecutionContext->valueRegistry->string(
								match($dep->unresolvableDependency) {
									UnresolvableDependency::circularDependency => 'Circular dependency',
									UnresolvableDependency::ambiguous => 'Ambiguous dependency',
									UnresolvableDependency::notFound => 'Dependency not found',
									UnresolvableDependency::unsupportedType => 'Unsupported type',
                                   UnresolvableDependency::errorWhileCreatingValue => 'Error returned while creating value',
								}
							)
						])
					)
				));
			}
			$dep = new TypedValue($this->dependencyType, $dep);
		}

		try {
			return new TypedValue(
				$this->returnType,
				$this->functionBody->execute(
					$this->methodExecutionContext->globalContext,
					TypedValue::forValue($targetValue),
					TypedValue::forValue($parameterValue),
					$dep
				)
			);
		} catch (FunctionReturn $result) {
			return TypedValue::forValue($result->value);
		}
	}

	public function __toString(): string {
		$dependency = $this->dependencyType ?
			sprintf(" using %s", $this->dependencyType) : '';
		return sprintf(
			"%s:%s ^%s => %s%s :: %s",
			$this->targetType,
			$this->methodName,
			$this->parameterType,
			$this->returnType,
			$dependency,
			$this->functionBody
		);
	}

	public function jsonSerialize(): array {
		return [
			'targetType' => (string)$this->targetType,
			'methodName' => $this->methodName,
			'parameterType' => $this->parameterType,
			'dependencyType' => $this->dependencyType,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}