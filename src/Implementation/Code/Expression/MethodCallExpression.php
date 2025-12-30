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
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression as MethodCallExpressionInterface;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;

final readonly class MethodCallExpression implements MethodCallExpressionInterface, JsonSerializable {

	use BaseTypeHelper;

	public function __construct(
		public Expression $target,
		public MethodNameIdentifier $methodName,
		public Expression $parameter,
	) {}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		try {
			$analyserContext = $this->target->analyse($analyserContext);
			$retTargetType = $analyserContext->expressionType;
			$targetReturnType = $analyserContext->returnType;

			$analyserContext = $this->parameter->analyse($analyserContext);
			$retParameterType = $analyserContext->expressionType;
			$parameterReturnType = $analyserContext->returnType;

			$method = $analyserContext->programRegistry->methodFinder->methodForType(
				$retTargetType,
				$this->methodName
			);
			if ($method instanceof UnknownMethod) {
				throw new AnalyserException(
					sprintf(
						"Cannot call method '%s' on type '%s'",
						$this->methodName,
						$retTargetType,
					),
					$this
				);
			}
			$retReturnType = $method->analyse(
				$analyserContext->programRegistry->typeRegistry,
				$analyserContext->programRegistry->methodFinder,
				$retTargetType,
				$retParameterType
			);
			return $analyserContext->asAnalyserResult(
				$retReturnType,
				$analyserContext->programRegistry->typeRegistry->union([
					$targetReturnType,
					$parameterReturnType
				])
			);
		} catch (AnalyserException $e) {
			if ($e->target === null) {
				$e = $e->withTarget($this);
			}
			throw $e;
		}
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return array_merge(
			$this->target->analyseDependencyType($dependencyContainer),
			$this->parameter->analyseDependencyType($dependencyContainer),
		);
	}

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->target->execute($executionContext);
		$retTargetValue = $executionContext->value;
		$retTargetType = $retTargetValue->type;

		$executionContext = $this->parameter->execute($executionContext);
		$retParameterTypedValue = $executionContext->value;

		$method = $executionContext->programRegistry->methodFinder->methodForValue(
			$retTargetValue,
			$this->methodName
		);
		if ($method instanceof UnknownMethod) {
			throw new ExecutionException(
				sprintf(
					"Cannot call method '%s' on type '%s' for value '%s'",
					$this->methodName,
					$retTargetType,
					$retTargetValue
				)
			);
		}
		try {
			$retReturnTypedValue = $method->execute(
				$executionContext->programRegistry,
				$retTargetValue,
				$retParameterTypedValue
			);
			return $executionContext->asExecutionResult($retReturnTypedValue);
            // @codeCoverageIgnoreStart
		} catch (ExecutionException $e) {
			throw new ExecutionException(
				sprintf("Execution error in method call %s->%s(%s) : \n %s",
					$this->target,
					$this->methodName,
					$this->parameter,
					$e->getMessage()
				),
				previous: $e
			);
		}
		// @codeCoverageIgnoreEnd
	}


	public function __toString(): string {
		$parameter = (string)$this->parameter;
		if (!($parameter[0] === '[' && $parameter[-1] === ']')) {
			$parameter = "($parameter)";
		}
		return sprintf(
			"%s->%s%s",
			$this->target,
			$this->methodName,
			$parameter === '(null)' ? '' : $parameter
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'methodCall',
			'target' => $this->target,
			'methodName' => $this->methodName,
			'parameter' => $this->parameter
		];
	}
}