<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Type\NameAndType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface FunctionContextFiller {

	public function fillAnalyserContext(
		AnalyserContext $analyserContext,
		Type $targetType,
		NameAndType $parameter,
		NameAndType $dependency,
	): AnalyserContext;

	public function fillExecutionContext(
		ExecutionContext $executionContext,
		Type $targetType,
		Value|null $targetValue,
		NameAndType $parameter,
		Value|null $parameterValue,
		NameAndType $dependency,
		Value|null $dependencyValue,
	): ExecutionContext;

}