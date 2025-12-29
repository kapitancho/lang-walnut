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
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsBoolean;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;

final readonly class BooleanXorExpression implements BooleanXorExpressionInterface, JsonSerializable {

	use BaseTypeHelper;

	private CastAsBoolean $castAsBoolean;

	public function __construct(
		public Expression $first,
		public Expression $second,
	) {
		$this->castAsBoolean = new CastAsBoolean();
	}

	private function analyseType(AnalyserContext $analyserContext, Type $type): Type {
		return $this->castAsBoolean->analyseType(
			$analyserContext->programRegistry->typeRegistry->boolean,
			$analyserContext->programRegistry->typeRegistry->true,
			$analyserContext->programRegistry->typeRegistry->false,
			$type
		);
	}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$firstAnalyserContext = $this->first->analyse($analyserContext);
		$firstExpressionType = $firstAnalyserContext->expressionType;
		$firstReturnType = $firstAnalyserContext->returnType;

		$firstBooleanType = $this->analyseType($analyserContext, $firstExpressionType);
		if ($firstExpressionType instanceof NothingType) {
			return $firstAnalyserContext;
		}

		$secondAnalyserContext = $this->second->analyse($firstAnalyserContext);
		$secondExpressionType = $secondAnalyserContext->expressionType;
		$secondReturnType = $secondAnalyserContext->returnType;

		$secondBooleanType = $this->analyseType($analyserContext, $secondExpressionType);
		if ($secondExpressionType instanceof NothingType) {
			return $secondAnalyserContext;
		}

		return $secondAnalyserContext->asAnalyserResult(
			match(true) {
				$firstBooleanType instanceof FalseType && $secondBooleanType instanceof FalseType,
				$firstBooleanType instanceof TrueType && $secondBooleanType instanceof TrueType =>
					$analyserContext->programRegistry->typeRegistry->false,
				$firstBooleanType instanceof FalseType && $secondBooleanType instanceof TrueType,
				$firstBooleanType instanceof TrueType && $secondBooleanType instanceof FalseType =>
					$analyserContext->programRegistry->typeRegistry->true,
				default =>
					$analyserContext->programRegistry->typeRegistry->boolean,
			},
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
		$firstValue = $this->castAsBoolean->evaluate($firstExecutionContext->value);
		$secondExecutionContext = $this->second->execute($firstExecutionContext);
		$secondValue = $this->castAsBoolean->evaluate($secondExecutionContext->value);

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