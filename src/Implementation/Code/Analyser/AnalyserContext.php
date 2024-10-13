<?php

namespace Walnut\Lang\Implementation\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AnalyserContext implements AnalyserContextInterface {

	public function __construct(
		private VariableScope $variableScope
	) {}

	public function variableScope(): VariableScope {
		return $this->variableScope;
	}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): self {
		return new self(
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