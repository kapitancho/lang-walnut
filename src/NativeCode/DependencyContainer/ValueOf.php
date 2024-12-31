<?php

namespace Walnut\Lang\NativeCode\DependencyContainer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class ValueOf implements NativeMethod {

	/** @noinspection PhpPropertyOnlyWrittenInspection */
	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
		private NativeCodeTypeMapper $typeMapper,
		private DependencyContainer $dependencyContainer,
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($parameterType instanceof TypeType) {
			return $this->context->typeRegistry->result(
				$parameterType->refType,
				$this->context->typeRegistry->withName(
					new TypeNameIdentifier('DependencyContainerError')
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$parameterValue = $parameter->value;
		
		if ($parameterValue instanceof TypeValue) {
			$type = $parameterValue->typeValue;
			$result = $this->dependencyContainer->valueByType($type);
			if ($result instanceof Value) {
				return new TypedValue($type, $result);
			}
			return TypedValue::forValue(
				$this->context->valueRegistry->error(
					$this->context->valueRegistry->sealedValue(
						new TypeNameIdentifier('DependencyContainerError'),
						$this->context->valueRegistry->record([
							'targetType' => $this->context->valueRegistry->type($type),
							'errorOnType' => $this->context->valueRegistry->type($result->type),
							'errorMessage' => $this->context->valueRegistry->string(
								match($result->unresolvableDependency) {
									UnresolvableDependency::circularDependency => 'Circular dependency',
									UnresolvableDependency::ambiguous => 'Ambiguous dependency',
									UnresolvableDependency::notFound => 'Dependency not found',
									UnresolvableDependency::unsupportedType => 'Unsupported type',
									UnresolvableDependency::errorWhileCreatingValue => 'Error returned while creating value',
								}
							)
						])
					)
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}