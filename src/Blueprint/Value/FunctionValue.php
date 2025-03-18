<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;

interface FunctionValue extends Value {

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void;

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext, Type $parameterType): Type;

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext, Value $parameterValue): Value;

	public FunctionType $type { get ;}

}