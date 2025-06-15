<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;

interface FunctionValue extends Value {

	public function withSelfReferenceAs(VariableNameIdentifier $variableName): self;

	public function withVariableValueScope(VariableValueScope $variableValueScope): self;

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext, Value $parameterValue): Value;

	public FunctionType $type { get ;}

}