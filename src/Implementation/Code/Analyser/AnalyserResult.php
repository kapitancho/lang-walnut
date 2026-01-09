<?php

namespace Walnut\Lang\Implementation\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AnalyserResult implements AnalyserResultInterface {

	public function __construct(
		public TypeRegistry $typeRegistry,
		public MethodAnalyser $methodAnalyser,
		public VariableScope $variableScope,
		public Type $expressionType,
		public Type $returnType
	) {}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): self {
		return new self(
			$this->typeRegistry,
			$this->methodAnalyser,
			$this->variableScope->withAddedVariableType($variableName, $variableType),
			$this->expressionType,
			$this->returnType
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResultInterface {
		return new self(
			$this->typeRegistry,
			$this->methodAnalyser,
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}

	public function returnType(): Type {
		return $this->returnType;
	}

	public function withExpressionType(Type $expressionType): AnalyserResultInterface {
		return new self(
			$this->typeRegistry,
			$this->methodAnalyser,
			$this->variableScope,
			$expressionType,
			$this->returnType
		);
	}

	public function withReturnType(Type $returnType): AnalyserResultInterface {
		return new self(
			$this->typeRegistry,
			$this->methodAnalyser,
			$this->variableScope,
			$this->expressionType,
			$returnType
		);
	}
}