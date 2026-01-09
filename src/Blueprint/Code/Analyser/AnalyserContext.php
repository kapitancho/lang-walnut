<?php

namespace Walnut\Lang\Blueprint\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;

interface AnalyserContext {
	public TypeRegistry $typeRegistry { get; }
	public MethodFinder $methodFinder { get; }
	public VariableScope $variableScope { get; }

	public function withAddedVariableType(
		VariableNameIdentifier $variableName,
		Type                   $variableType,
	): self;

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResult;
}