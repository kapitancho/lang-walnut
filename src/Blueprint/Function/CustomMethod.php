<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethod extends Method {
	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void;

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array;

	public MethodNameIdentifier $methodName { get; }

	public Type $targetType { get; }
	public VariableNameIdentifier|null $parameterName { get; }
	public Type $parameterType { get; }
	public Type $returnType { get; }
	public VariableNameIdentifier|null $dependencyName { get; }
	public Type $dependencyType { get; }

	public string $methodInfo { get; }
}