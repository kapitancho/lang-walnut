<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser as MethodAnalyserInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class MethodAnalyser implements MethodAnalyserInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private MethodRegistry $methodRegistry,
	) {}

	public function methodForType(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		return $this->methodRegistry->methodForType($targetType, $methodName);
	}

	public function safeAnalyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type|UnknownMethod {
		$method = $this->methodRegistry->methodForType(
			$targetType,
			$methodName
		);
		if ($method instanceof UnknownMethod) {
			return $method;
		}
		return $method->analyse(
			$this->typeRegistry,
			$this,
			$targetType,
			$parameterType
		);
	}

	/** @throws AnalyserException */
	public function analyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type {
		$result = $this->safeAnalyseMethod($targetType, $methodName, $parameterType);
		if ($result instanceof UnknownMethod) {
			throw new AnalyserException(
				sprintf(
					"Cannot call method '%s' on type '%s'",
					$methodName,
					$targetType,
				)
			);
		}
		return $result;
	}

}