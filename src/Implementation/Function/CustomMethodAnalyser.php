<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\CustomMethodAnalyser as CustomMethodAnalyserInterface;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class CustomMethodAnalyser implements CustomMethodAnalyserInterface {
	public function __construct(
		private ProgramRegistry $programRegistry,
		private NativeCodeTypeMapper $nativeCodeTypeMapper,
	) {}

	public function analyse(CustomMethodRegistry $registry): array {
		$analyseErrors = [];
		$customizedTypes = [];
		foreach($registry->customMethods as $methods) {
			foreach ($methods as $method) {
				$customizedTypes[(string)$method->targetType] = $method->targetType;
			}
		}
		$customizedTypeCandidates = array_map(fn(Type $customizedType): array =>
			$this->nativeCodeTypeMapper->getTypesFor($customizedType), $customizedTypes);

		foreach($registry->customMethods as $mn => $methods) {
			$methodName = new MethodNameIdentifier($mn);
			$methodFnTypes = [];
			$methodTargetTypes = [];
			foreach ($methods as $method) {
				$methodTargetTypes[] = $methodTargetType = $method->targetType;
				$methodFnTypes[] = $this->programRegistry->typeRegistry->function(
					$method->parameterType,
					$method->returnType
				);
				$usedMethods = [];
				foreach(array_reverse($customizedTypeCandidates[(string)$methodTargetType] ?? []) as $customizedTypeCandidate) {
					$customizedTypeCandidateType = $this->programRegistry->typeRegistry->typeByName(
						new TypeNameIdentifier($customizedTypeCandidate)
					);
					$baseMethod = $this->programRegistry->methodFinder->methodForType(
						$customizedTypeCandidateType,
						$methodName
					);
					if ($customizedTypeCandidateType instanceof AnyType && $methodName->equals(new MethodNameIdentifier('asString'))) {
						$usedMethods[$baseMethod::class] = true;
						continue;
					}
					if ($baseMethod instanceof NativeMethod && !array_key_exists($baseMethod::class, $usedMethods)) {
						$baseReturnType = $baseMethod->analyse(
							$this->programRegistry,
							$methodTargetType,
							$method->parameterType,
						);
						if (!$method->returnType->isSubtypeOf($baseReturnType)) {
							$analyseErrors[] = sprintf("%s : the method %s is already defined for %s but its return type < %s > is not a subtype of < %s > when called with parameter of type %s ",
								$this->getErrorMessageFor($method),
								$methodName,
								$customizedTypeCandidateType,
								$method->returnType,
								$baseReturnType,
								$method->parameterType
							);
						}
					}
					$usedMethods[$baseMethod::class] = true;
				}
			}

			$cnt = count($methodFnTypes);
			for ($k = 0; $k < $cnt; $k++) {
				for ($j = 0; $j < $cnt; $j++) if ($k !== $j) {
					if ($methodTargetTypes[$k]->isSubtypeOf($methodTargetTypes[$j]) &&
						!$methodFnTypes[$k]->isSubtypeOf($methodFnTypes[$j])) {
						$analyseErrors[] = sprintf("%s : the method %s is already defined for %s and therefore the signature %s should be a subtype of %s",
							$this->getErrorMessageFor($methods[$k]),
							$methodName,
							$methodTargetTypes[$j],
							$methodFnTypes[$k],
							$methodFnTypes[$j]
						);
					}
				}
			}
			foreach ($methods as $method) {
				try {
					$sub = $method->targetType;
					$methodFnType = $this->programRegistry->typeRegistry->function(
						$method->parameterType,
						$method->returnType
					);
					$method->selfAnalyse($this->programRegistry);
				} catch (AnalyserException $e) {
					$analyseErrors[] = sprintf("%s : %s", $this->getErrorMessageFor($method), $e->getMessage());
					continue;
				}
			}
		}
		if (count($analyseErrors) === 0) {
			foreach ($registry->customMethods as $methods) {
				foreach ($methods as $method) {
					$d = $method->dependencyType;
					if (!($d instanceof NothingType)) {
						$value = $this->programRegistry->dependencyContainer->valueByType($method->dependencyType);
						if ($value instanceof DependencyError) {
							$analyseErrors[] = sprintf("%s : the dependency %s cannot be resolved: %s (type: %s)",
								$this->getErrorMessageFor($method),
								$method->dependencyType,
								match ($value->unresolvableDependency) {
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