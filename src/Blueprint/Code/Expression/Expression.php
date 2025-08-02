<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Stringable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

interface Expression extends Stringable {
	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult;

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array;

	/** @throws ExecutionException|FunctionReturn */
	public function execute(ExecutionContext $executionContext): ExecutionResult;
}