<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface FunctionBody extends Stringable {
	public Expression $expression { get; }

	/** @throws AnalyserException */
	public function analyse(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType
	): Type;

	/** @throws ExecutionException */
	public function execute(
		ExecutionContext $executionContext,
		TypedValue|null $targetValue,
		TypedValue $parameterValue,
		VariableNameIdentifier|null $parameterName,
		TypedValue|null $dependencyValue
	): Value;
}