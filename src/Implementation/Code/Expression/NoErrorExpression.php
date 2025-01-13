<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\NoErrorExpression as NoErrorExpressionInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Value\ErrorValue;

final readonly class NoErrorExpression implements NoErrorExpressionInterface, JsonSerializable {
	public function __construct(
		public Expression $targetExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$ret = $this->targetExpression->analyse($analyserContext);
		$expressionType = $ret->expressionType;
		if ($expressionType instanceof ResultType) {
			return $ret->withExpressionType(
				$expressionType->returnType
			)->withReturnType(
				$analyserContext->programRegistry->typeRegistry->result(
					$ret->returnType,
					$expressionType->errorType
				)
			);
		}
		return $ret;
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$result = $this->targetExpression->execute($executionContext);
		$value = $result->value;
		if ($value instanceof ErrorValue) {
			throw new FunctionReturn($value);
		}
		$vt = $result->valueType;
		// @codeCoverageIgnoreStart
		if ($vt instanceof ResultType) {
			$result = $result->withTypedValue(new TypedValue($vt->returnType, $result->value));
		}
		// @codeCoverageIgnoreEnd
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"?noError(%s)",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'noError',
			'targetExpression' => $this->targetExpression
		];
	}
}