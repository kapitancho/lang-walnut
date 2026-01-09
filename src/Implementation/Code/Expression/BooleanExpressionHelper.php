<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

trait BooleanExpressionHelper {

	private function getBooleanType(AnalyserContext $analyserContext, Type $expressionType): Type {
		return $analyserContext->methodContext->analyseMethod(
			$expressionType,
			new MethodNameIdentifier('asBoolean'),
			$analyserContext->typeRegistry->null
		);
	}

	private function getBooleanValue(ExecutionContext $executionContext, Value $value): bool {
		return $executionContext->methodContext->executeMethod(
			$value,
			new MethodNameIdentifier('asBoolean'),
			$executionContext->valueRegistry->null
		)->equals(
			$executionContext->valueRegistry->true
		);
	}

}