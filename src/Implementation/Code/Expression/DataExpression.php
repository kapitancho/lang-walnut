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
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\UnknownType;

final readonly class DataExpression implements DataExpressionInterface, JsonSerializable {

	public function __construct(
		public TypeNameIdentifier $typeName,
		public Expression         $value
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserContext = $this->value->analyse($analyserContext);
		try {
			$dataType = $analyserContext->typeRegistry->complex->data($this->typeName);
		} catch (UnknownType) {
			throw new AnalyserException(
				sprintf("The data type '%s' is not defined.", $this->typeName),
				$this
			);
		}
		if (!$analyserContext->expressionType->isSubtypeOf($dataType->valueType)) {
			throw new AnalyserException(
				sprintf(
					"The data type '%s' expected base value of type '%s', but got '%s'.",
					$this->typeName,
					$dataType->valueType,
					$analyserContext->expressionType
				),
				$this
			);
		}
		return $analyserContext->withExpressionType(
			$analyserContext->typeRegistry->complex->data(
				$this->typeName
			)
		);
	}


	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->value->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->value->execute($executionContext);
		$dataType = $executionContext->typeRegistry->complex->data($this->typeName);
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
			$executionContext->valueRegistry->dataValue(
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