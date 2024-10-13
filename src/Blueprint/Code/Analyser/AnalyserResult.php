<?php

namespace Walnut\Lang\Blueprint\Code\Analyser;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface AnalyserResult extends AnalyserContext {
	public function expressionType(): Type;
	public function returnType(): Type;
	public function withAddedVariableType(
		VariableNameIdentifier $variableName,
		Type                   $variableType,
	): self;
	public function withExpressionType(Type $expressionType): self;
	public function withReturnType(Type $returnType): self;
}