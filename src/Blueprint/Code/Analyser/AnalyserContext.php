<?php

namespace Walnut\Lang\Blueprint\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface AnalyserContext {
	public VariableScope $variableScope { get; }

	public function withAddedVariableType(
		VariableNameIdentifier $variableName,
		Type                   $variableType,
	): self;

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResult;
}