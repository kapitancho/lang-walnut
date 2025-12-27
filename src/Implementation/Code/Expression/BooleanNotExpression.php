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
use Walnut\Lang\Blueprint\Code\Expression\BooleanNotExpression as BooleanNotExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsBoolean;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;

final readonly class BooleanNotExpression implements BooleanNotExpressionInterface, JsonSerializable {

	use BaseTypeHelper;

	private CastAsBoolean $castAsBoolean;

	public function __construct(
		public Expression $expression
	) {
		$this->castAsBoolean = new CastAsBoolean();
	}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserResult = $this->expression->analyse($analyserContext);
		$expressionType = $this->castAsBoolean->analyseType(
			$analyserContext->programRegistry->typeRegistry->boolean,
			$analyserContext->programRegistry->typeRegistry->true,
			$analyserContext->programRegistry->typeRegistry->false,
			$analyserResult->expressionType
		);

		return $analyserResult->withExpressionType(match(true) {
			$expressionType instanceof FalseType => $analyserContext->programRegistry->typeRegistry->true,
			$expressionType instanceof TrueType => $analyserContext->programRegistry->typeRegistry->false,
			default => $analyserContext->programRegistry->typeRegistry->boolean
		});
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->expression->analyseDependencyType($dependencyContainer);
	}

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->expression->execute($executionContext);
		$value = $this->castAsBoolean->evaluate($executionContext->value);

		return $executionContext->withValue(
			$executionContext->programRegistry->valueRegistry->boolean(!$value)
		);
	}

	public function __toString(): string {
		return sprintf(
			"!{%s}",
			$this->expression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'BooleanNot',
			'first' => $this->expression
		];
	}
}