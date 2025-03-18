<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface UserlandFunction {
	public Type $targetType { get; }
	public Type $parameterType { get; }
	public Type $returnType { get; }
	public FunctionBody $functionBody { get; }

	/** @throws AnalyserException */
	public function selfAnalyse(
		AnalyserContext $analyserContext
	): void;

	/** @throws AnalyserException */
	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type;

	/** @throws ExecutionException */
	public function execute(
		ExecutionContext            $executionContext,
		Value|null $targetValue,
		Value      $parameterValue,
	): Value;

}