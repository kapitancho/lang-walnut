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
use Walnut\Lang\Blueprint\Code\Expression\DataExpression as DataExpressionInterface;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class DataExpression implements DataExpressionInterface, JsonSerializable {

	public function __construct(
		public TypeNameIdentifier $typeName,
		public Expression         $value
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserContext = $this->value->analyse($analyserContext);
		$dataType = $analyserContext->programRegistry->typeRegistry->data($this->typeName);
		if (!$analyserContext->expressionType->isSubtypeOf($dataType->valueType)) {
			throw new AnalyserException(
				sprintf(
					"The data type '%s' expected base value of type '%s', but got '%s'.",
					$this->typeName,
					$dataType->valueType,
					$analyserContext->expressionType
				)
			);
		}
		return $analyserContext->withExpressionType(
			$analyserContext->programRegistry->typeRegistry->data(
				$this->typeName
			)
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->value->execute($executionContext);
		$dataType = $executionContext->programRegistry->typeRegistry->data($this->typeName);
		if (!$executionContext->value->type->isSubtypeOf($dataType->valueType)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf(
					"The data type '%s' expected base value of type '%s', but got '%s'.",
					$this->typeName,
					$dataType->valueType,
					$executionContext->value
				)
			);
			// @codeCoverageIgnoreEnd
		}
		return $executionContext->withValue(
			$executionContext->programRegistry->valueRegistry->dataValue(
				$this->typeName,
				$executionContext->value
			)
		);
	}

	public function __toString(): string {
		return sprintf(
			"%s!%s",
			$this->typeName,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Data',
			'typeName' => $this->typeName,
			'value' => $this->value
		];
	}
}