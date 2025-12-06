<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Value\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class CompositeFunctionValue implements FunctionValueInterface, JsonSerializable {

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly FunctionValueInterface $first,
		public readonly FunctionValueInterface $second,
	) {}

	public function composeWith(FunctionValueInterface $nextFunction): FunctionValueInterface {
		return new CompositeFunctionValue(
			$this->typeRegistry,
			$this,
			$nextFunction
		);
	}

	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		$analyseErrors = [];
		$firstErrors = $this->first->analyseDependencyType($dependencyContainer);
		if (count($firstErrors) > 0) {
			$analyseErrors = array_merge($analyseErrors, $firstErrors);
		}
		$secondErrors = $this->second->analyseDependencyType($dependencyContainer);
		if (count($secondErrors) > 0) {
			$analyseErrors = array_merge($analyseErrors, $secondErrors);
		}
		return $analyseErrors;
	}

	public function withVariableValueScope(VariableValueScopeInterface $variableValueScope): self {
		return $this;
	}

	public function withSelfReferenceAs(VariableNameIdentifier $variableName): self {
		return $this;
	}

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		$this->first->selfAnalyse($analyserContext);
		$this->second->selfAnalyse($analyserContext);
	}

	public FunctionType $type {
		get => $this->typeRegistry->function(
			$this->first->type->parameterType,
			$this->second->type->returnType
		);
	}

	public function execute(ExecutionContext $executionContext, Value $parameterValue): Value {
		$intermediateValue = $this->first->execute($executionContext, $parameterValue);
		return $this->second->execute($executionContext, $intermediateValue);
	}

	public function equals(Value $other): bool {
		return $other instanceof self &&
			$this->first->equals($other->first) &&
			$this->second->equals($other->second);
	}

	public function __toString(): string {
		return md5($this->first . ' + ' . $this->second);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'CompositeFunction',
			'first' => $this->first,
			'second' => $this->second,
		];
	}
}