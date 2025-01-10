<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\CustomMethodAnalyser as CustomMethodAnalyserInterface;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\SubtypeType;

final readonly class CustomMethodAnalyser implements CustomMethodAnalyserInterface {
	public function __construct(
		private ProgramRegistry $programRegistry,
	) {}

	public function analyse(CustomMethodRegistry $registry): array {
		$analyseErrors = [];
		foreach($registry->customMethods as $methods) {
			foreach($methods as $method) {
				try {
					$sub = $method->targetType;
					$methodFnType = $this->programRegistry->typeRegistry->function(
						$method->parameterType,
						$method->returnType
					);
					while ($sub instanceof SubtypeType || $sub instanceof AliasType) {
						if ($sub instanceof AliasType) {
							$sub = $sub->aliasedType;
							continue;
						}
						$sub = $sub->baseType;
						$existingMethod = $this->programRegistry->methodRegistry->method($sub, $method->methodName);
						if ($existingMethod instanceof CustomMethodInterface) {
							$existingMethodFnType = $this->programRegistry->typeRegistry->function(
								$existingMethod->parameterType,
								$existingMethod->returnType
							);
							if (!$methodFnType->isSubtypeOf($existingMethodFnType)) {
								$analyseErrors[] = sprintf("%s : the method %s is already defined for %s and therefore the signature %s should be a subtype of %s",
									$this->getErrorMessageFor($method),
									$existingMethod->methodName,
									$sub,
									$methodFnType,
									$existingMethodFnType
								);
								break;
							}
						} elseif ($existingMethod instanceof Method) {
							$analyseResult = $existingMethod->analyse($this->programRegistry, $method->targetType, $method->parameterType);
							if (!$method->returnType->isSubtypeOf($analyseResult)) {
								$analyseErrors[] = sprintf("%s : the method %s is already defined for %s and therefore the return type %s should be a subtype of %s",
									$this->getErrorMessageFor($method),
									$method->methodName,
									$sub,
									$method->returnType,
									$analyseResult
								);
								break;
							}
						}
					}
					$method->analyse(
						$this->programRegistry,
						$method->targetType,
						$method->parameterType,
					);
				} catch (AnalyserException $e) {
					$analyseErrors[] = sprintf("%s : %s",$this->getErrorMessageFor($method), $e->getMessage());
					continue;
				}
				$d = $method->dependencyType;
				if (!($d instanceof NothingType)) {
					$value = $this->programRegistry->dependencyContainer->valueByType($method->dependencyType);
					if ($value instanceof DependencyError) {
						$analyseErrors[] = sprintf("%s : the dependency %s cannot be resolved: %s (type: %s)",
							$this->getErrorMessageFor($method),
							$method->dependencyType,
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

	private function getErrorMessageFor(CustomMethodInterface $method): string {
		return match(true) {
			(string)$method->targetType === 'Constructor' && str_starts_with($method->methodName->identifier, 'as')
				=> sprintf("Error in the validator of %s", substr($method->methodName, 2)),
			(string)$method->targetType === 'Constructor'
				=> sprintf("Error in the constructor of %s", $method->methodName),
			(string)$method->targetType === 'DependencyContainer'
				=> sprintf("Error in the dependency builder of %s", substr($method->methodName, 2)),
			(string)$method->targetType === 'Global'
				=> sprintf("Error in global function %s", $method->methodName),
			str_starts_with($method->methodName->identifier, 'as')
				=> sprintf("Error in the cast %s ==> %s", $method->targetType,
					substr($method->methodName, 2)),
			default => sprintf("Error in method %s->%s", $method->targetType, $method->methodName)
		};
	}

}