<?php

namespace Walnut\Lang\NativeCode\DependencyContainer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class ValueOf implements NativeMethod {

	/** @noinspection PhpPropertyOnlyWrittenInspection */
	public function __construct(
		private NativeCodeTypeMapper $typeMapper,
	) {}

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($parameterType instanceof TypeType) {
			return $programRegistry->typeRegistry->result(
				$parameterType->refType,
				$programRegistry->typeRegistry->withName(
					new TypeNameIdentifier('DependencyContainerError')
				)
			);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$parameterValue = $parameter;
		
		if ($parameterValue instanceof TypeValue) {
			$type = $parameterValue->typeValue;
			$result = $programRegistry->dependencyContainer->valueByType($type);
			if ($result instanceof Value) {
				return $result;
			}
			return (
				$programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier('DependencyContainerError'),
						$programRegistry->valueRegistry->record([
							'targetType' => $programRegistry->valueRegistry->type($type),
							'errorOnType' => $programRegistry->valueRegistry->type($result->type),
							'errorMessage' => $programRegistry->valueRegistry->string(
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