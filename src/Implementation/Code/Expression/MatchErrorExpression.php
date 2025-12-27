<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchErrorExpression as MatchErrorExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
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
					// @codeCoverageIgnoreStart
					$analyserContext->programRegistry->typeRegistry->any;
					// @codeCoverageIgnoreEnd

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

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return array_merge(
			$this->target->analyseDependencyType($dependencyContainer),
			$this->onError->analyseDependencyType($dependencyContainer),
			$this->else ? $this->else->analyseDependencyType($dependencyContainer) : [],
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->target->execute($executionContext);
		$typedValue = $executionContext->value;
		if ($typedValue instanceof ErrorValue) {
			$innerContext = $executionContext;
			if ($this->target instanceof VariableNameExpression) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$typedValue
				);
			}
			return $executionContext->withValue(
				$this->onError->execute($innerContext)->value
			);
		}
		if ($this->else) {
			$innerContext = $executionContext;
			if ($this->target instanceof VariableNameExpression) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$typedValue
				);
			}
			return $executionContext->withValue(
				$this->else->execute($innerContext)->value
			);
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