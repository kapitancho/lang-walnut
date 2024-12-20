<?php

namespace Walnut\Lang\Implementation\Code\Analyser;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AnalyserResult implements AnalyserResultInterface {

	public function __construct(
		private VariableScope $variableScope,
		private Type          $expressionType,
		private Type          $returnType
	) {}

	public function variableScope(): VariableScope {
		return $this->variableScope;
	}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): self {
		return new self(
			$this->variableScope->withAddedVariableType($variableName, $variableType),
			$this->expressionType,
			$this->returnType
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResultInterface {
		return new self(
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}

	public function expressionType(): Type {
		return $this->expressionType;
	}

	public function returnType(): Type {
		return $this->returnType;
	}

	public function withExpressionType(Type $expressionType): AnalyserResultInterface {
		return new self(
			$this->variableScope,
			$expressionType,
			$this->returnType
		);
	}

	public function withReturnType(Type $returnType): AnalyserResultInterface {
		return new self(
			$this->variableScope,
			$this->expressionType,
			$returnType
		);
	}
}