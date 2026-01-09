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
use Walnut\Lang\Blueprint\Code\Expression\BooleanXorExpression as BooleanXorExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;

final readonly class BooleanXorExpression implements BooleanXorExpressionInterface, JsonSerializable {

	use BaseTypeHelper;
	use BooleanExpressionHelper;

	public function __construct(
		public Expression $first,
		public Expression $second,
	) {}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$firstAnalyserContext = $this->first->analyse($analyserContext);
		$firstExpressionType = $firstAnalyserContext->expressionType;
		$firstReturnType = $firstAnalyserContext->returnType;

		$firstBooleanType = $this->getBooleanType($analyserContext, $firstExpressionType);
		if ($firstExpressionType instanceof NothingType) {
			return $firstAnalyserContext;
		}

		$secondAnalyserContext = $this->second->analyse($firstAnalyserContext);
		$secondExpressionType = $secondAnalyserContext->expressionType;
		$secondReturnType = $secondAnalyserContext->returnType;

		$secondBooleanType = $this->getBooleanType($analyserContext, $secondExpressionType);
		if ($secondExpressionType instanceof NothingType) {
			return $secondAnalyserContext;
		}

		return $secondAnalyserContext->asAnalyserResult(
			match(true) {
				$firstBooleanType instanceof FalseType && $secondBooleanType instanceof FalseType,
				$firstBooleanType instanceof TrueType && $secondBooleanType instanceof TrueType =>
					$analyserContext->typeRegistry->false,
				$firstBooleanType instanceof FalseType && $secondBooleanType instanceof TrueType,
				$firstBooleanType instanceof TrueType && $secondBooleanType instanceof FalseType =>
					$analyserContext->typeRegistry->true,
				default =>
					$analyserContext->typeRegistry->boolean,
			},
			$analyserContext->typeRegistry->union([
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
		$secondExecutionContext = $this->second->execute($firstExecutionContext);
		$secondValue = $this->getBooleanValue($secondExecutionContext, $secondExecutionContext->value);

		return $secondExecutionContext->withValue(
			($firstValue && !$secondValue) || (!$firstValue && $secondValue) ?
				$executionContext->programRegistry->valueRegistry->true :
				$executionContext->programRegistry->valueRegistry->false
		);
	}

	public function __toString(): string {
		return sprintf(
			"{%s} ^^ {%s}",
			$this->first,
			$this->second
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'BooleanXor',
			'first' => $this->first,
			'second' => $this->second
		];
	}
}