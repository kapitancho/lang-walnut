<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

interface ProgramRegistry {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }
	public MethodFinder $methodFinder { get; }
	public VariableValueScope $globalScope { get; }

	public AnalyserContext $analyserContext { get; }
	public ExecutionContext $executionContext { get; }

	public DependencyContainer $dependencyContainer { get; }
}