<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use JsonSerializable;
use Walnut\Lang\Blueprint\AST\Compiler\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\CustomMethodDraft as CustomMethodDraftInterface;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
use Walnut\Lang\Blueprint\Function\MethodDraft;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Registry\MethodDraftRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Function\CustomMethod;
use Walnut\Lang\Implementation\Function\CustomMethodDraft;

final class CustomMethodRegistryBuilder implements CustomMethodRegistryBuilderInterface, MethodDraftRegistry, JsonSerializable {

	/**
	 * @var array<string, list<CustomMethodDraftInterface>> $methods
	 */
	private array $methods;

	public function __construct(
		private readonly MethodExecutionContext $methodExecutionContext,
		private readonly DependencyContainer    $dependencyContainer,
		private readonly MethodDraftRegistry    $methodDraftRegistry,
		private readonly TypeRegistry           $typeRegistry
	) {
		$this->methods = [];
	}

	public function addMethodDraft(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBodyDraft $functionBody,
	): CustomMethodDraftInterface {
		$this->methods[$methodName->identifier] ??= [];
		$this->methods[$methodName->identifier][] = $method = new CustomMethodDraft(
			$targetType,
			$methodName,
			$parameterType,
			$dependencyType,
			$returnType,
			$functionBody,
		);
		return $method;
	}

	public function build(AstFunctionBodyCompiler $astFunctionBodyCompiler): MethodRegistry {
		$methods = array_map(
			fn(array $methods) => array_map(
				fn(CustomMethodDraftInterface $method) => new CustomMethod(
					$this->methodExecutionContext,
					$this->dependencyContainer,
					$method->targetType,
					$method->methodName,
					$method->parameterType,
					$method->dependencyType,
					$method->returnType,
					$astFunctionBodyCompiler->functionBody($method->functionBody)
				),
				$methods
			),
			$this->methods
		);
		$analyseErrors = $this->checkVariance($methods);
		if (count($analyseErrors) > 0) {
			throw new AnalyserException(implode("\n", $analyseErrors));
		}
		//$this->analyse($methods);
		return new CustomMethodRegistry($methods);
	}

	private function getErrorMessageFor(CustomMethodInterface $method): string {
		return match(true) {
			(string)$method->targetType === 'Constructor' && str_starts_with($method->methodName->identifier, 'as')
				=> sprintf("Error in the validator of %s", substr($method->methodName, 2)),
			(string)$method->targetType === 'Constructor'
				=> sprintf("Error in the constructor of %s", $method->methodName),
			(string)$method->targetType === 'DependencyContainer'
				=> sprintf("Error in the dependency builder of %s", substr($method->methodName, 2)),
			str_starts_with($method->methodName->identifier, 'as')
				=> sprintf("Error in the cast %s ==> %s", $method->targetType,
					substr($method->methodName, 2)),
			default => sprintf("Error in method %s->%s", $method->targetType, $method->methodName)
		};
	}

	/** @return string[] - the errors found during analyse */
	public function checkVariance(array $allMethods): array {
		$analyseErrors = [];
		foreach($allMethods as $methods) {
			foreach($methods as $method) {
				$sub = $method->targetType;
				$methodFnType = $this->typeRegistry->function(
					$method->parameterType,
					$method->returnType
				);
				while ($sub instanceof SubtypeType || $sub instanceof AliasType) {
					if ($sub instanceof AliasType) {
						$sub = $sub->aliasedType;
						continue;
					}
					$sub = $sub->baseType;
					$existingMethod = $this->methodDraftRegistry->method($sub, $method->methodName);
					if ($existingMethod instanceof CustomMethodDraftInterface) {
						$existingMethodFnType = $this->typeRegistry->function(
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
					} elseif ($existingMethod instanceof MethodDraft) {
						$analyseResult = $existingMethod->analyse($method->targetType, $method->parameterType);
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
			}
		}
		return $analyseErrors;
	}

	/** @return string[] - the errors found during analyse */
	public function analyse(array $allMethods): array {
		$analyseErrors = [];
		foreach($allMethods as $methods) {
			foreach($methods as $method) {
				try {
					$sub = $method->targetType;
					$methodFnType = $this->typeRegistry->function(
						$method->parameterType,
						$method->returnType
					);
					while ($sub instanceof SubtypeType || $sub instanceof AliasType) {
						if ($sub instanceof AliasType) {
							$sub = $sub->aliasedType;
							continue;
						}
						$sub = $sub->baseType;
						$existingMethod = $this->methodDraftRegistry->method($sub, $method->methodName);
						if ($existingMethod instanceof CustomMethodDraftInterface) {
							$existingMethodFnType = $this->typeRegistry->function(
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
						} elseif ($existingMethod instanceof MethodDraft) {
							$analyseResult = $existingMethod->analyse($method->targetType, $method->parameterType);
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
						$method->targetType,
						$method->parameterType,
					);
				} catch (AnalyserException $e) {
					$analyseErrors[] = sprintf("%s : %s",$this->getErrorMessageFor($method), $e->getMessage());
					continue;
				}
				$d = $method->dependencyType;
				if ($d && !($d instanceof NothingType)) {
					$value = $this->dependencyContainer->valueByType($method->dependencyType);
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

	public function jsonSerialize(): array {
		return $this->methods;
	}

	public function methodDraft(Type $targetType, MethodNameIdentifier $methodName): MethodDraft|UnknownMethod {
		foreach(array_reverse($this->methods[$methodName->identifier] ?? []) as $method) {
			if ($targetType->isSubtypeOf($method->targetType)) {
				return $method;
			}
		}
		return UnknownMethod::value;
	}
}