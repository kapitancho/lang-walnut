<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Function\FunctionBody as FunctionBodyInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class FunctionBody implements FunctionBodyInterface {
	public function __construct(
		public Expression $expression
	) {}

	/** @throws AnalyserException */
	public function analyse(
		AnalyserContext $analyserContext
	): Type {
		$analyserResult = $this->expression->analyse(
			$analyserContext
		);
		return $analyserContext->programRegistry->typeRegistry->union([
			$analyserResult->expressionType,
			$analyserResult->returnType
		]);
	}

	/** @throws ExecutionException */
	public function execute(
		ExecutionContext $executionContext
	): Value {
		try {
			$executionResult = $this->expression->execute(
				$executionContext
			);
			return $executionResult->value;
		} catch (FunctionReturn $return) {
			return $return->typedValue;
		}
	}

	public function __toString(): string {
		return (string)$this->expression;
	}

}