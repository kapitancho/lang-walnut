<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\ResultType;

final readonly class CastAs implements NativeMethod {
	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
		private NativeCodeTypeMapper $typeMapper,
	) {}

	/**
	 * @return array{0: string, 1: Method}|UnknownMethod
	 */
	private function getMethod(
		TypeInterface $targetType,
		TypeInterface $parameterType
	): array|UnknownMethod {
		foreach($this->typeMapper->getTypesFor($parameterType) as $candidate) {
			$method = $this->methodRegistry->method($targetType,
				$methodName = new MethodNameIdentifier(sprintf('as%s',
					$candidate
				))
			);
			if ($method instanceof Method) {
				return [$methodName, $method];
			}
		}
		return UnknownMethod::value;
	}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType
	): TypeInterface {
		if ($parameterType instanceof TypeType) {
			$refType = $parameterType->refType();
			if ($targetType->isSubtypeOf($refType)) {
				return $refType;
			}
			$method = $this->getMethod($targetType, $refType);
			if ($method instanceof UnknownMethod) {
				return $this->context->typeRegistry()->result(
					$refType,
					$this->context->typeRegistry()->withName(new TypeNameIdentifier('CastNotAvailable'))
				);
				/*throw new AnalyserException(
					sprintf(
						"Cannot cast type %s to %s",
						$targetType,
						$refType
					)
				);*/
			}
			$returnType = $method[1]->analyse(
				$targetType,
				$this->context->typeRegistry()->nothing()
			);
			$resultType = $returnType instanceof ResultType ? $returnType->returnType() : $returnType;

			if (!$resultType->isSubtypeOf($refType)) {
				throw new AnalyserException(sprintf(
					"Cast method '%s' returns '%s' which is not a subtype of '%s'",
					$method[0],
					$resultType,
					$refType
				));
			}
			return $refType instanceof AliasType ? $refType : $returnType;
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		if ($parameterValue instanceof TypeValue) {
			if ($targetValue->type()->isSubtypeOf($parameterValue->typeValue())) {
				return new TypedValue($parameterValue->typeValue(), $targetValue);
			}
			$runtimeMethod = $this->getMethod(
				$targetValue instanceof TypeValue ?
					$targetValue->typeValue() :
					$targetValue->type(),
				$parameterValue->typeValue()
			);
			if ($runtimeMethod instanceof UnknownMethod) {
				$method = $this->getMethod(
					$targetType = $target->type,
					$parameterType = $parameterValue->typeValue()
				);
				if ($method instanceof UnknownMethod) {
					$val = $this->context->valueRegistry()->error(
						$this->context->valueRegistry()->sealedValue(
							new TypeNameIdentifier('CastNotAvailable'),
							$this->context->valueRegistry()->record([
								'from' => $this->context->valueRegistry()->type($targetType),
								'to' => $this->context->valueRegistry()->type($parameterType)
							])
						)
					);
					return TypedValue::forValue($val);
				}
				return $method[1]->execute(
					$target,
					$parameter
				);
			}
			return $runtimeMethod[1]->execute(
				$target,
				$parameter
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}