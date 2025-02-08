<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyException;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;

interface FunctionValue extends Value {
	public FunctionType $type { get; }
	public VariableNameIdentifier|null $parameterName { get; }
	public Type $parameterType { get; }
	public Type $dependencyType { get; }
	public Type $returnType { get; }
	public FunctionBody $body { get; }

	public function withVariableValueScope(VariableValueScope $variableValueScope): self;
	public function withSelfReferenceAs(VariableNameIdentifier $variableName): self;

	/** @throws FunctionBodyException */
	public function analyse(AnalyserContext $analyserContext): void;
	public function execute(ExecutionContext $executionContext, Value $value): Value;
}