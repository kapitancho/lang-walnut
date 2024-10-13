<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

interface MethodExecutionContext {
	public function typeRegistry(): TypeRegistry;
	public function valueRegistry(): ValueRegistry;
	public function globalContext(): AnalyserContext&ExecutionContext;
}