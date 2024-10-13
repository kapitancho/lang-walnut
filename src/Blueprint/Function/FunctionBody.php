<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface FunctionBody extends Stringable {
	public function expression(): Expression;

	public function analyse(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		Type $dependencyType
	): Type;

	public function execute(
		ExecutionContext $executionContext,
		TypedValue|null $targetValue,
		TypedValue $parameterValue,
		TypedValue|null $dependencyValue
	): Value;
}