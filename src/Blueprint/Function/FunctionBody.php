<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface FunctionBody extends Stringable {
	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): Type;

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array;

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): Value;
}