<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchErrorExpression as MatchErrorExpressionInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MatchErrorExpression implements MatchErrorExpressionInterface, JsonSerializable {
	use BaseType;

	public function __construct(
		public Expression $target,
		public Expression $onError,
		public Expression|null $else,
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$retTarget = $this->target->analyse($analyserContext);
		$bType = $this->toBaseType($retTarget->expressionType);

		$returnTypes = [$retTarget->returnType];
		$onErrorExpressionType = $analyserContext->programRegistry->typeRegistry->nothing;
		$elseExpressionType = $bType instanceof ResultType ? $bType->returnType :
			$analyserContext->programRegistry->typeRegistry->any;

		if ($retTarget->expressionType->isSubtypeOf(
			$analyserContext->programRegistry->typeRegistry->result(
				$analyserContext->programRegistry->typeRegistry->any,
				$analyserContext->programRegistry->typeRegistry->any
			)
		)) {
			$innerContext = $retTarget;
			if ($this->target instanceof VariableNameExpression) {
				$errorType = $bType instanceof ResultType ? $bType->errorType :
					$analyserContext->programRegistry->typeRegistry->any;

				$innerContext = $innerContext->withAddedVariableType(
					$this->target->variableName,
					$analyserContext->programRegistry->typeRegistry->result(
						$analyserContext->programRegistry->typeRegistry->nothing,
						$errorType
					),
				);
			}
			$retValue = $this->onError->analyse($innerContext);

			$onErrorExpressionType = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
		}
		if ($this->else) {
			$innerContext = $retTarget;
			if ($this->target instanceof VariableNameExpression) {
				$errorType = $bType instanceof ResultType ? $bType->errorType :
					$analyserContext->programRegistry->typeRegistry->any;

				$innerContext = $innerContext->withAddedVariableType(
					$this->target->variableName,
					$elseExpressionType,
				);
			}
			$retValue = $this->else->analyse($innerContext);
			$elseExpressionType = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
		}

		return $retTarget->asAnalyserResult(
			$analyserContext->programRegistry->typeRegistry->union([
				$onErrorExpressionType,
				$elseExpressionType
			]),
			$analyserContext->programRegistry->typeRegistry->union($returnTypes)
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->target->execute($executionContext);
		$typedValue = $executionContext->typedValue;
		if ($typedValue->value instanceof ErrorValue) {
			$innerContext = $executionContext;
			if ($this->target instanceof VariableNameExpression) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$typedValue
				);
			}
			return $this->onError->execute($innerContext);
		}
		if ($this->else) {
			$innerContext = $executionContext;
			if ($this->target instanceof VariableNameExpression) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$typedValue
				);
			}
			return $this->else->execute($innerContext);
		}
		return $executionContext;
	}

	public function __toString(): string {
		return sprintf("?whenIsError (%s) { %s }%s",
			$this->target,
			$this->onError,
			$this->else ? sprintf(" ~ { %s }", $this->else) : ''
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'MatchError',
			'target' => $this->target,
			'onError' => $this->onError,
			'else' => $this->else
		];
	}
}