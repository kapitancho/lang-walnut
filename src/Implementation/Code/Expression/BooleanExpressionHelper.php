<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

trait BooleanExpressionHelper {

	private function getBooleanType(AnalyserContext $analyserContext, Type $expressionType): Type {
		$pr = $analyserContext->programRegistry;
		$mf = $pr->methodFinder;
		$tr = $pr->typeRegistry;
		$method = $mf->methodForType(
			$expressionType,
			new MethodNameIdentifier('asBoolean')
		);
		return $method->analyse($tr, $mf, $expressionType, $tr->null);
	}

	private function getBooleanValue(ExecutionContext $executionContext, Value $value): bool {
		$pr = $executionContext->programRegistry;
		$mf = $pr->methodFinder;
		$method = $mf->methodForType(
			$value->type,
			new MethodNameIdentifier('asBoolean')
		);
		return $method->execute($pr, $value, $pr->valueRegistry->null)->equals(
			$executionContext->programRegistry->valueRegistry->true
		);
	}

}