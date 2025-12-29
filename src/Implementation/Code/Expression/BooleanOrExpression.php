<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\BooleanOrExpression as BooleanOrExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;
use Walnut\Lang\Implementation\Type\Helper\VariableScopeHelper;

final readonly class BooleanOrExpression implements BooleanOrExpressionInterface, JsonSerializable {

	use BaseTypeHelper;
	use BooleanExpressionHelper;
	use VariableScopeHelper;

	public function __construct(
		public Expression $first,
		public Expression $second,
	) {}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$firstAnalyserContext = $this->first->analyse($analyserContext);
		$firstExpressionType = $firstAnalyserContext->expressionType;
		if ($firstExpressionType instanceof NothingType) {
			return $firstAnalyserContext;
		}
		$firstReturnType = $firstAnalyserContext->returnType;
		$firstBooleanType = $this->getBooleanType($analyserContext, $firstExpressionType);
		if ($firstBooleanType instanceof TrueType) {
			return $firstAnalyserContext->withExpressionType($firstBooleanType);
		}

		$secondAnalyserContext = $this->second->analyse($firstAnalyserContext);
		$secondExpressionType = $secondAnalyserContext->expressionType;
		$secondReturnType = $secondAnalyserContext->returnType;

		$secondBooleanType = $this->getBooleanType($analyserContext, $secondExpressionType);

		if (!$this->scopeVariablesMatch(
			$firstAnalyserContext->variableScope,
			$secondAnalyserContext->variableScope
		)) {
			throw new AnalyserException(
				"Variable scopes do not match between first and second expressions in boolean OR operation."
			);
		}

		return $this->contextUnion($firstAnalyserContext, $secondAnalyserContext)->asAnalyserResult(
			$secondBooleanType,
			$analyserContext->programRegistry->typeRegistry->union([
				$firstReturnType,
				$secondReturnType
			])
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return array_merge(
			$this->first->analyseDependencyType($dependencyContainer),
			$this->second->analyseDependencyType($dependencyContainer),
		);
	}

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$firstExecutionContext = $this->first->execute($executionContext);
		$firstValue = $this->getBooleanValue($firstExecutionContext, $firstExecutionContext->value);

		if ($firstValue) {
			return $firstExecutionContext->withValue(
				$firstExecutionContext->programRegistry->valueRegistry->boolean(true)
			);
		}
		$secondExecutionContext = $this->second->execute($firstExecutionContext);
		return $secondExecutionContext->withValue(
			$secondExecutionContext->programRegistry->valueRegistry->boolean(
				$this->getBooleanValue($secondExecutionContext, $secondExecutionContext->value)
			)
		);
	}

	public function __toString(): string {
		return sprintf(
			"{%s} || {%s}",
			$this->first,
			$this->second
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'BooleanOr',
			'first' => $this->first,
			'second' => $this->second
		];
	}
}