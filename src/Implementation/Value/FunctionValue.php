<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyException;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\FunctionType;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final class FunctionValue implements FunctionValueInterface, JsonSerializable {
	use TupleAsRecord;

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly ValueRegistry $valueRegistry,
		private readonly DependencyContainer $dependencyContainer,
		public readonly Type $parameterType,
		public readonly Type $dependencyType,
		public readonly Type $returnType,
		public readonly FunctionBody $body,
	    private readonly VariableValueScopeInterface|null $variableValueScope,
	    private readonly VariableNameIdentifier|null $selfReferAs
    ) {}

	public function withVariableValueScope(VariableValueScopeInterface $variableValueScope): self {
		return new self(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->dependencyContainer,
			$this->parameterType,
			$this->dependencyType,
			$this->returnType,
			$this->body,
			$variableValueScope,
			$this->selfReferAs
		);
	}

	public function withSelfReferenceAs(VariableNameIdentifier $variableName): self {
		return new self(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->dependencyContainer,
			$this->parameterType,
			$this->dependencyType,
			$this->returnType,
			$this->body,
			$this->variableValueScope,
			$variableName
		);
	}

	public FunctionType $type {
		get => $this->typeRegistry->function(
			$this->parameterType,
			$this->returnType
        );
    }

	/** @throws FunctionBodyException */
	public function analyse(AnalyserContext $analyserContext): void {
		foreach ($this->variableValueScope?->allTypes() ?? [] as $variableName => $type) {
			/** @noinspection PhpParamsInspection */ //PhpStorm bug
			$analyserContext = $analyserContext->withAddedVariableType($variableName, $type);
		}
		if ($this->selfReferAs) {
			$analyserContext = $analyserContext->withAddedVariableType(
				$this->selfReferAs,
				$this->typeRegistry->function(
					$this->parameterType,
					$this->returnType
				),
			);
		}
		//try {
			$returnType = $this->body->analyse(
				$analyserContext,
				$this->typeRegistry->nothing,
				$this->parameterType,
				$this->dependencyType,
			);
			if (!($this->dependencyType instanceof NothingType)) {
				$value = $this->dependencyContainer->valueByType($this->dependencyType);
				if ($value instanceof DependencyError) {
					throw new AnalyserException(
						sprintf("Dependency %s cannot be instantiated", $this->dependencyType)
					);
				}
			}
		//} catch (AnalyserException $ex) {
		//	throw new FunctionBodyException($ex->getMessage());
		//}
		if (!$returnType->isSubtypeOf($this->returnType)) {
			throw new FunctionBodyException(
				sprintf(
					"Function return type \n %s is not a subtype of \n %s",
					$returnType,
					$this->returnType
				)
			);
		}
	}

	public function execute(ExecutionContext $executionContext, Value $value): Value {
		foreach ($this->variableValueScope?->allTypedValues() ?? [] as $variableName => $type) {
			/** @noinspection PhpParamsInspection */ //PhpStorm bug
			$executionContext = $executionContext->withAddedVariableValue($variableName, $type);
		}
		if ($this->selfReferAs) {
			$executionContext = $executionContext->withAddedVariableValue(
				$this->selfReferAs,
				new TypedValue(
					$this->typeRegistry->function(
						$this->parameterType,
						$this->returnType
					),
					$this
				)
			);
		}
		if ($value instanceof TupleValue &&
			$this->parameterType instanceof RecordType &&
			$this->isTupleCompatibleToRecord(
				$this->typeRegistry,
				$value->type,
				$this->parameterType
			)
		) {
			$value = $this->getTupleAsRecord($this->valueRegistry, $value, $this->parameterType);
		}

		try {
			/*$dependencyValue = $this->dependencyType instanceof NothingType ? null :
				$this->dependencyContainer->valueByType($this->dependencyType);*/
			return $this->body->execute(
				$executionContext,
				null,
				new TypedValue($this->parameterType, $value),
				//$dependencyValue instanceof Value ? new TypedValue($this->dependencyType, $dependencyValue) : null
				$this->dependencyType instanceof NothingType ?
					null : new TypedValue(
						$this->dependencyType,
						$this->dependencyContainer->valueByType($this->dependencyType)
					)
			);
		} catch (FunctionReturn $result) {
			return $result->value;
		}
	}

	public function equals(Value $other): bool {
		return $other instanceof self && (string)$this === (string)$other;
	}

	public function __toString(): string {
		return sprintf(
			"^%s => %s :: %s",
			$this->parameterType,
			$this->returnType,
			$this->body
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Function',
			'parameterType' => $this->parameterType,
			'returnType' => $this->returnType,
			'body' => $this->body
		];
	}
}