<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\ResultType;

final readonly class CastAs implements NativeMethod {
	public function __construct(
		private NativeCodeTypeMapper $typeMapper,
	) {}

	/**
	 * @return array{0: string, 1: Method}|UnknownMethod
	 */
	private function getMethod(
		MethodRegistry $methodRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType
	): array|UnknownMethod {
		$isParameterJson = $parameterType instanceof AliasType && $parameterType->name->equals(
			new TypeNameIdentifier('JsonValue')
		);
		foreach($this->typeMapper->getTypesFor($parameterType) as $candidate) {
			if (!$isParameterJson && $candidate === 'JsonValue') {
				//The JsonValue type should be ignored because there is a
				//generic method asJsonValue which returns a different value (and type)
				continue;
			}
			$method = $methodRegistry->methodForType($targetType,
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
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType
	): TypeInterface {
		if ($parameterType instanceof TypeType) {
			$refType = $parameterType->refType;
			if ($targetType->isSubtypeOf($refType)) {
				return $refType;
			}
			$method = $this->getMethod($programRegistry->methodFinder, $targetType, $refType);
			if ($method instanceof UnknownMethod) {
				return $programRegistry->typeRegistry->result(
					$refType,
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('CastNotAvailable'))
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
				$programRegistry,
				$targetType,
				$programRegistry->typeRegistry->nothing
			);
			$resultType = $returnType instanceof ResultType ? $returnType->returnType : $returnType;

			if (!$resultType->isSubtypeOf($refType)) {
				throw new AnalyserException(sprintf(
					"Cast method '%s' returns '%s' which is not a subtype of '%s'",
					$method[0],
					$resultType,
					$refType
				));
			}
			return $refType instanceof AliasType ? (
				$returnType instanceof ResultType ? $programRegistry->typeRegistry->result(
					$refType,
					$returnType->errorType
				) : $refType
			) : $returnType;
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		if ($parameterValue instanceof TypeValue) {
			if ($targetValue->type->isSubtypeOf($parameterValue->typeValue)) {
				return $target;
			}

			$method = $programRegistry->methodFinder->methodForValue(
				$target,
				new MethodNameIdentifier(sprintf('as%s',
					 $parameterValue->typeValue
				))
			);
			if ($method instanceof UnknownMethod) {
				$val = $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->openValue(
						new TypeNameIdentifier('CastNotAvailable'),
						$programRegistry->valueRegistry->record([
							'from' => $programRegistry->valueRegistry->type($target->type),
							'to' => $programRegistry->valueRegistry->type($parameterValue->typeValue)
						])
					)
				);
				return TypedValue::forValue($val);
			}
			return $method->execute(
				$programRegistry,
				$target,
				TypedValue::forValue($programRegistry->valueRegistry->null)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}