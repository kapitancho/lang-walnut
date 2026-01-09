<?php

namespace Walnut\Lang\Implementation\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AnalyserContext implements AnalyserContextInterface {

	public function __construct(
		public TypeRegistry $typeRegistry,
		public MethodAnalyser $methodAnalyser,
		public VariableScope $variableScope
	) {}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): self {
		return new self(
			$this->typeRegistry,
			$this->methodAnalyser,
			$this->variableScope->withAddedVariableType($variableName, $variableType)
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResult {
		return new AnalyserResult(
			$this->typeRegistry,
			$this->methodAnalyser,
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}
}