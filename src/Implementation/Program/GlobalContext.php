<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserResult;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionResult;

final class GlobalContext implements AnalyserContextInterface, ExecutionContextInterface {

	public function __construct(
		private readonly ScopeBuilder $scopeBuilder
	) {}

	public VariableValueScope $variableValueScope {
		get {
			return $this->scopeBuilder->build();
		}
	}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, TypedValue $typedValue): ExecutionContext {
		return new ExecutionContext(
			$this->variableValueScope->withAddedVariableValue($variableName, $typedValue)
		);
	}

	public function asExecutionResult(TypedValue $typedValue): ExecutionResult {
		return new ExecutionResult(
			$this->variableValueScope,
			$typedValue
		);
	}

	public VariableScope $variableScope {
		get {
			return $this->scopeBuilder->build();
		}
	}

	public function withAddedVariableType(
		VariableNameIdentifier $variableName,
		Type $variableType
	): AnalyserContextInterface {
		return new AnalyserContext(
			$this->variableScope->withAddedVariableType($variableName, $variableType)
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResult {
		return new AnalyserResult(
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}
}