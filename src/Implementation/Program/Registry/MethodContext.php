<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext as MethodContextInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class MethodContext implements MethodContextInterface {

	public function __construct(
		private ProgramRegistry $programRegistry,
		private MethodFinder    $methodFinder,
	) {}

	/** @throws AnalyserException */
	public function analyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type {
		$method = $this->methodFinder->methodForType(
			$targetType,
			$methodName
		);
		if ($method instanceof UnknownMethod) {
			throw new AnalyserException(
				sprintf(
					"Cannot call method '%s' on type '%s'",
					$methodName,
					$targetType,
				)
			);
		}
		return $method->analyse(
			$this->programRegistry->typeRegistry,
			$this->methodFinder,
			$targetType,
			$parameterType
		);
	}

	/** @throws ExecutionException */
	public function executeMethod(
		Value $target,
		MethodNameIdentifier $methodName,
		Value $parameter
	): Value {
		$method = $this->methodFinder->methodForValue(
			$target,
			$methodName
		);
		if ($method instanceof UnknownMethod) {
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
		return $method->execute(
			$this->programRegistry,
			$target,
			$parameter
		);
	}

}