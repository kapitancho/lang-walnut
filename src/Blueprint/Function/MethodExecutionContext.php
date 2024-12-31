<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

interface MethodExecutionContext {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }
	public AnalyserContext&ExecutionContext $globalContext { get; }
}