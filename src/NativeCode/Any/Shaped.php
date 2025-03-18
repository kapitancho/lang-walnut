<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Shaped implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof TypeType) {
			$refType = $this->toBaseType($parameterType->refType);
			if ($refType instanceof ShapeType) {
				$parameterType = $refType;
			}
			if ($targetType->isSubtypeOf($parameterType->refType)) {
				return $programRegistry->typeRegistry->shape($parameterType->refType);
			}
			$targetType = $this->toBaseType($targetType);
			if ($targetType instanceof OpenType && $targetType->valueType->isSubtypeOf($parameterType->refType)) {
				return $programRegistry->typeRegistry->shape($parameterType->refType);
			}
			$method = $programRegistry->methodFinder->methodForType(
				$targetType,
				new MethodNameIdentifier('castAs')
			);
			if ($method instanceof Method) {
				try {
					$resultType = $method->analyse(
						$programRegistry,
						$targetType,
						$parameterType,
					);
					$shapeReturn = $programRegistry->typeRegistry->shape($parameterType->refType);
					if ($resultType instanceof ResultType) {
						throw new AnalyserException(sprintf(
							"[%s] Incompatible shape: %s shaped as %s cannot be cast. An error value of type %s is possible.",
							__CLASS__, $targetType, $parameterType->refType, $resultType->errorType));
						/*$shapeReturn = $programRegistry->typeRegistry->result(
							$shapeReturn,
							$resultType->errorType
						);*/
					}
					return $shapeReturn;
				} catch (AnalyserException $exception) {
					// @codeCoverageIgnoreStart
					throw new AnalyserException(sprintf(
						"[%s] Incompatible shape: %s shaped as %s cannot be cast: %s",
						__CLASS__, $targetType, $parameterType->refType, $exception->getMessage()));
					// @codeCoverageIgnoreEnd
				}
			}
			throw new AnalyserException(sprintf("[%s] Incompatible shape: %s shaped as %s",
				__CLASS__, $targetType, $parameterType->refType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		if ($parameter->value instanceof TypeValue) {
			return $target;
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
    }

}