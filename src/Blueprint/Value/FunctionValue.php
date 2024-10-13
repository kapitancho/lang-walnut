<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyException;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;

interface FunctionValue extends Value {
	public function withVariableValueScope(VariableValueScope $variableValueScope): self;
	public function withSelfReferenceAs(VariableNameIdentifier $variableName): self;

    public function type(): FunctionType;
    public function parameterType(): Type;
    public function dependencyType(): Type;
    public function returnType(): Type;
    public function body(): FunctionBody;

	/** @throws FunctionBodyException */
	public function analyse(AnalyserContext $analyserContext): void;
	public function execute(ExecutionContext $executionContext, Value $value): Value;
}