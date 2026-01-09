<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

interface ProgramRegistry {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }
	public MethodContext $methodContext { get; }

	public AnalyserContext $analyserContext { get; }
	public ExecutionContext $executionContext { get; }

	public DependencyContainer $dependencyContainer { get; }
}