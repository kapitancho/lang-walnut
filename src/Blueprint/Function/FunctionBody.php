<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Type\Type;

interface FunctionBody extends Stringable {
	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): Type;

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): TypedValue;
}