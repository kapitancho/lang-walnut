<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionCompositionMode;
use Walnut\Lang\Blueprint\Value\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;

final class CompositeFunctionValue implements FunctionValueInterface, JsonSerializable {

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly FunctionValueInterface $first,
		public readonly FunctionValueInterface $second,
		public readonly FunctionCompositionMode $compositionMode
	) {}

	public function composeWith(FunctionValueInterface $nextFunction, FunctionCompositionMode $compositionMode): FunctionValueInterface {
		return new CompositeFunctionValue(
			$this->typeRegistry,
			$this,
			$nextFunction,
			$compositionMode
		);
	}

	public FunctionType $type {
		get => $this->typeRegistry->function(
			$this->first->type->parameterType,
			$this->second->type->returnType
		);
	}

	// @codeCoverageIgnoreStart
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

	public function execute(ExecutionContext $executionContext, Value $parameterValue): Value {
		$intermediateValue = $this->first->execute($executionContext, $parameterValue);
		if ($this->compositionMode === FunctionCompositionMode::orElse) {
			return $intermediateValue instanceof ErrorValue ?
				$this->second->execute($executionContext, $parameterValue) :
				$intermediateValue;
		}
		if ($intermediateValue instanceof ErrorValue) {
			if ($this->compositionMode === FunctionCompositionMode::bypassErrors || (
				$this->compositionMode === FunctionCompositionMode::bypassExternalErrors &&
				$intermediateValue->errorValue instanceof SealedValue &&
				$intermediateValue->errorValue->type->name->equals(
					new TypeNameIdentifier('ExternalError')
				)
			)) {
				return $intermediateValue;
			}
		}
		return $this->second->execute($executionContext, $intermediateValue);
	}

	public function equals(Value $other): bool {
		return $other instanceof self &&
			$this->first->equals($other->first) &&
			$this->second->equals($other->second) &&
			$this->compositionMode === $other->compositionMode;
	}

	public function __toString(): string {
		return sprintf("{{%s} %s {%s}}",
			$this->first,
			match($this->compositionMode) {
				FunctionCompositionMode::direct => '+',
				FunctionCompositionMode::bypassErrors => '&',
				FunctionCompositionMode::bypassExternalErrors => '*',
				FunctionCompositionMode::orElse => '|',
			},
			$this->second,
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'CompositeFunction',
			'first' => $this->first,
			'second' => $this->second,
		];
	}
	// @codeCoverageIgnoreEnd

}