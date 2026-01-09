<?php

namespace Walnut\Lang\Implementation\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AnalyserContext implements AnalyserContextInterface {

	public function __construct(
		public TypeRegistry $typeRegistry,
		public MethodContext $methodContext,
		public VariableScope $variableScope
	) {}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): self {
		return new self(
			$this->typeRegistry,
			$this->methodContext,
			$this->variableScope->withAddedVariableType($variableName, $variableType)
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResult {
		return new AnalyserResult(
			$this->typeRegistry,
			$this->methodContext,
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}
}