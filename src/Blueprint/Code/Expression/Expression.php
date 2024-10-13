<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Stringable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;

interface Expression extends Stringable {
	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult;

	/** @throws FunctionReturn */
	public function execute(ExecutionContext $executionContext): ExecutionResult;
}