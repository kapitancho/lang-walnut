<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Value\Value;

interface MethodContext extends MethodAnalyser {
	/** @throws ExecutionException */
	public function executeMethod(
		Value $target,
		MethodNameIdentifier $methodName,
		Value $parameter
	): Value;
}