<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Function\CustomMethod;

final class CustomMethodRegistryBuilder implements MethodRegistry, CustomMethodRegistryBuilderInterface, JsonSerializable {

	/**
	 * @var array<string, list<CustomMethodInterface>> $methods
	 */
	private array $methods;

	public function __construct(
		private readonly MethodExecutionContext $methodExecutionContext,
		private readonly DependencyContainer $dependencyContainer
	) {
		$this->methods = [];
	}

	public function addMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $functionBody,
	): CustomMethodInterface {
		$this->methods[$methodName->identifier] ??= [];
		$this->methods[$methodName->identifier][] = $method = new CustomMethod(
			$this->methodExecutionContext,
			$this->dependencyContainer,
			$targetType,
			$methodName,
			$parameterType,
			$dependencyType,
			$returnType,
			$functionBody,
		);
		return $method;
	}

	public function method(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		foreach(array_reverse($this->methods[$methodName->identifier] ?? []) as $method) {
			if ($targetType->isSubtypeOf($method->targetType())) {
				return $method;
			}
		}
		return UnknownMethod::value;
	}

	private function getErrorMessageFor(CustomMethodInterface $method): string {
		return match(true) {
			(string)$method->targetType() === 'Constructor'
				=> sprintf("Error in the constructor of %s", $method->methodName()),
			(string)$method->targetType() === 'DependencyContainer'
				=> sprintf("Error in the dependency builder of %s", substr($method->methodName(), 2)),
			str_starts_with($method->methodName()->identifier, 'as')
				=> sprintf("Error in the cast %s ==> %s", $method->targetType(),
					substr($method->methodName(), 2)),
			default => sprintf("Error in method %s->%s", $method->targetType(), $method->methodName())
		};
	}

	/** @return string[] - the errors found during analyse */
	public function analyse(): array {
		$analyseErrors = [];
		foreach($this->methods as $methods) {
			foreach($methods as $method) {
				try {
					$method->analyse(
						$method->targetType(),
						$method->parameterType(),
					);
				} catch (AnalyserException $e) {
					$analyseErrors[] = sprintf("%s : %s",$this->getErrorMessageFor($method), $e->getMessage());
				}
				$d = $method->dependencyType();
				if ($d && !($d instanceof NothingType)) {
					$value = $this->dependencyContainer->valueByType($method->dependencyType());
					if ($value instanceof DependencyError) {
						$analyseErrors[] = sprintf("%s : the dependency %s cannot be resolved: %s (type: %s)",
							$this->getErrorMessageFor($method),
							$method->dependencyType(),
							match($value->unresolvableDependency) {
								UnresolvableDependency::notFound => "no appropriate value found",
								UnresolvableDependency::ambiguous => "ambiguity - multiple values found",
								UnresolvableDependency::circularDependency => "circular dependency detected",
								UnresolvableDependency::unsupportedType => "unsupported type found",
                                UnresolvableDependency::errorWhileCreatingValue => 'error returned while creating value',
							},
							$value->type
						);
					}
				}
			}
		}
		return $analyseErrors;
	}

	public function jsonSerialize(): array {
		return $this->methods;
	}
}