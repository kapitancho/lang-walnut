<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\NameAndType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface UserlandFunction {
	public string $displayName { get; }

	public Type $targetType { get; }

	public NameAndType $parameter { get; }
	public NameAndType $dependency { get; }
	public Type $returnType { get; }
	public FunctionBody $functionBody { get; }

	/** @throws AnalyserException */
	public function selfAnalyse(
		AnalyserContext $analyserContext
	): void;

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array;

	/** @throws AnalyserException */
	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type;

	/** @throws ExecutionException */
	public function execute(
		ExecutionContext $executionContext,
		Value|null $targetValue,
		Value $parameterValue,
	): Value;

}