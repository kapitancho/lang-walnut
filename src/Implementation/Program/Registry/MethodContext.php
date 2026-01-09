<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser as MethodAnalyserInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext as MethodContextInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class MethodContext implements MethodContextInterface {

	public function __construct(
		private ProgramRegistry         $programRegistry,
		private MethodFinder            $methodFinder,
		private MethodAnalyserInterface $methodAnalyser,
	) {}

	public function methodForType(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		return $this->methodAnalyser->methodForType($targetType, $methodName);
	}

	public function safeAnalyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type|UnknownMethod {
		return $this->methodAnalyser->safeAnalyseMethod(
			$targetType,
			$methodName,
			$parameterType
		);
	}

	/** @throws AnalyserException */
	public function analyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type {
		return $this->methodAnalyser->analyseMethod(
			$targetType,
			$methodName,
			$parameterType
		);
	}

	public function methodForValue(Value $target, MethodNameIdentifier $methodName): Method|UnknownMethod {
		return $this->methodFinder->methodForValue($target, $methodName);
	}

	public function safeExecuteMethod(
		Value $target,
		MethodNameIdentifier $methodName,
		Value $parameter
	): Value|UnknownMethod {
		$method = $this->methodFinder->methodForValue(
			$target,
			$methodName
		);
		if ($method instanceof UnknownMethod) {
			return $method;
		}
		return $method->execute(
			$this->programRegistry,
			$target,
			$parameter
		);
	}

	/** @throws ExecutionException */
	public function executeMethod(
		Value $target,
		MethodNameIdentifier $methodName,
		Value $parameter
	): Value {
		$result = $this->safeExecuteMethod(
			$target,
			$methodName,
			$parameter
		);
		if ($result instanceof UnknownMethod) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf(
					"Cannot call method '%s' on type '%s' for value '%s'",
					$methodName,
					$target->type,
					$target
				)
			);
			// @codeCoverageIgnoreEnd
		}
		return $result;
	}

}