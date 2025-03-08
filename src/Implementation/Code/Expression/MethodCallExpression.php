<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression as MethodCallExpressionInterface;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;
use Walnut\Lang\Implementation\Type\NothingType;

final readonly class MethodCallExpression implements MethodCallExpressionInterface {

	use BaseTypeHelper;

	public function __construct(
		public Expression $target,
		public MethodNameIdentifier $methodName,
		public Expression $parameter,
	) {}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
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

				)
			);
		}
		$retReturnType = $method->analyse(
			$analyserContext->programRegistry,
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
	}

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->target->execute($executionContext);
		$retTargetTypedValue = $executionContext->typedValue;
		$retTargetType = $executionContext->valueType;

		$executionContext = $this->parameter->execute($executionContext);
		$retParameterTypedValue = $executionContext->typedValue;

		$method = $executionContext->programRegistry->methodFinder->methodForValue(
			$retTargetTypedValue,
			$this->methodName
		);
		if ($method instanceof UnknownMethod) {
			throw new ExecutionException(
				sprintf(
					"Cannot call method '%s' on type '%s' for value '%s'",
					$this->methodName,
					$retTargetType,
					$retTargetTypedValue->value
				)
			);
		}
		try {
			$retReturnTypedValue = $method->execute(
				$executionContext->programRegistry,
				$retTargetTypedValue,
				$retParameterTypedValue
			);
			//TODO - DependencyContainerError
			return $executionContext->asExecutionResult($retReturnTypedValue);
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