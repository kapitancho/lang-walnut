<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext as MethodExecutionContextInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

final readonly class MethodExecutionContext implements MethodExecutionContextInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private AnalyserContext&ExecutionContext $globalExecutionContext
	) {}

	public function typeRegistry(): TypeRegistry {
		return $this->typeRegistry;
	}

	public function valueRegistry(): ValueRegistry {
		return $this->valueRegistry;
	}

	public function globalContext(): AnalyserContext&ExecutionContext {
		return $this->globalExecutionContext;
	}
}