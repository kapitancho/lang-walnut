<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\CustomMethodAnalyser as CustomMethodAnalyserInterface;
use Walnut\Lang\Blueprint\Function\CustomMethodAnalyserException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class CustomMethodAnalyser implements CustomMethodAnalyserInterface {
	public function __construct(
		private ProgramRegistry $programRegistry,
		private NativeCodeTypeMapper $nativeCodeTypeMapper,
	) {}

	private function typeByName(TypeNameIdentifier $typeName): Type {
		$tr = $this->programRegistry->typeRegistry;
		return match($typeName->identifier) {
			//'Union' => $tr->metaType(MetaTypeValue::Union),
			//'Intersection' => $tr->metaType(MetaTypeValue::Intersection),
			'Record' => $tr->metaType(MetaTypeValue::Record),
			'Tuple' => $tr->metaType(MetaTypeValue::Tuple),
			//'Alias' => $tr->metaType(MetaTypeValue::Alias),
			'Function' => $tr->metaType(MetaTypeValue::Function),
			default => $this->programRegistry->typeRegistry->typeByName($typeName)
		};
	}

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
					$customizedTypeCandidateType = $this->typeByName(
						new TypeNameIdentifier($customizedTypeCandidate)
					);
					$baseMethod = $this->programRegistry->methodContext->methodForType(
						$customizedTypeCandidateType,
						$methodName
					);
					if ($customizedTypeCandidateType instanceof AnyType && $methodName->equals(new MethodNameIdentifier('asString'))) {
						$usedMethods[$baseMethod::class] = true;
						continue;
					}
					if ($baseMethod instanceof NativeMethod && !array_key_exists($baseMethod::class, $usedMethods)) {
						try {
							$baseReturnType = $baseMethod->analyse(
								$this->programRegistry->typeRegistry,
								$this->programRegistry->methodContext,
								$methodTargetType,
								$method->parameterType,
							);
						} catch (AnalyserException $e) {
							$analyseErrors[] = new CustomMethodAnalyserException(
								sprintf("Error in %s", $method->methodInfo),
								$e,
								$method
							);
							continue 2;
						}
						if (!$method->returnType->isSubtypeOf($baseReturnType)) {
							$analyseErrors[] = new CustomMethodAnalyserException(
								sprintf("Error in %s", $method->methodInfo),
								sprintf(
									"the method %s is already defined for %s but its return type '%s' is not a subtype of '%s' when called with parameter of type %s ",
									$methodName,
									$customizedTypeCandidateType,
									$method->returnType,
									$baseReturnType,
									$method->parameterType
								),
								$method
							);
						}
					}
					$usedMethods[$baseMethod::class] = true;
				}
			}

			$cnt = count($methodFnTypes);
			for ($k = 0; $k < $cnt; $k++) {
				for ($j = 0; $j < $cnt; $j++) { if ($k !== $j) {
                        if ($methodTargetTypes[$k]->isSubtypeOf($methodTargetTypes[$j]) &&
						!$methodFnTypes[$k]->isSubtypeOf($methodFnTypes[$j])) {

                            $analyseErrors[] = new CustomMethodAnalyserException(
							sprintf("Error in %s", $methods[$k]->methodInfo),
							sprintf(
								"the method %s is already defined for %s and therefore the signature %s should be a subtype of %s",
								$methodName,
								$methodTargetTypes[$j],
								$methodFnTypes[$k],
								$methodFnTypes[$j]
							),
							$methods[$k]
                            );
                        }
				}
                }
			}

			$analyserContext = $this->programRegistry->analyserContext;
			foreach ($methods as $method) {
				try {
					/*$sub = $method->targetType;
					$methodFnType = $this->programRegistry->typeRegistry->function(
						$method->parameterType,
						$method->returnType
					);*/
					$method->selfAnalyse($analyserContext);
				} catch (AnalyserException $e) {
					$analyseErrors[] =
						new CustomMethodAnalyserException(
							sprintf("Error in %s", $method->methodInfo),
							$e,
							$method
						);
					continue;
				}
			}
		}
		if (count($analyseErrors) === 0) {
			foreach ($registry->customMethods as $methods) {
				foreach ($methods as $method) {
					$errors = $method->analyseDependencyType($this->programRegistry->dependencyContainer);
					if (count($errors) > 0) {
						$analyseErrors = array_merge(
							$analyseErrors,
							array_map(
								fn(string $error) => new CustomMethodAnalyserException(
									sprintf("Error in %s", $method->methodInfo),
									$error,
									$method
								),
								$errors
							)
						);
					}
				}
			}
		}
		return $analyseErrors;
	}

}