<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final class CustomMethod implements CustomMethodInterface {
	use TupleAsRecord;

	public function __construct(
		public readonly UserlandFunction $function,
		public readonly MethodNameIdentifier $methodName,
	) {}

	/** @throws AnalyserException */
	public function selfAnalyse(ProgramRegistry $programRegistry): void {
		$this->function->selfAnalyse($programRegistry->analyserContext);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		$analyseErrors = [];
		$d = $this->dependencyType;
		if (!($d instanceof NothingType)) {
			$value = $dependencyContainer->valueByType($d);
			if ($value instanceof DependencyError) {
				$analyseErrors[] = sprintf("Error in %s : the dependency %s cannot be resolved: %s (type: %s)",
					$this->methodInfo,
					$d,
					$value->unresolvableDependency->errorInfo(),
					$value->type
				);
			}
		}
		$functionErrors = $this->function->analyseDependencyType($dependencyContainer);
		if (count($functionErrors) > 0) {
			$analyseErrors = array_merge($analyseErrors, $functionErrors);
		}
		return $analyseErrors;
	}

	/** @throws AnalyserException */
	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		return $this->function->analyse(
			$targetType,
			$this->adjustParameterType(
				$programRegistry->typeRegistry,
				$this->function->parameterType,
				$parameterType
			)
		);
	}

	/** @throws ExecutionException */
	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->function->execute(
			$programRegistry->executionContext,
			$target,
			$this->adjustParameterValue(
				$programRegistry->valueRegistry,
				$this->function->parameterType,
				$parameter,
			)
		);
	}

	public Type $targetType { get => $this->function->targetType; }
	public Type $parameterType { get => $this->function->parameterType; }
	public Type $returnType { get => $this->function->returnType; }
	public Type $dependencyType { get => $this->function->dependencyType; }

	public string $methodInfo {
		get => match(true) {
			(string)$this->targetType === 'Constructor' && str_starts_with($this->methodName->identifier, 'as')
			=> sprintf("the validator of %s", substr($this->methodName, 2)),
			(string)$this->targetType === 'Constructor'
			=> sprintf("the constructor of %s", $this->methodName),
			(string)$this->targetType === 'DependencyContainer'
			=> sprintf("the dependency builder of %s", substr($this->methodName, 2)),
			str_starts_with($this->methodName->identifier, 'as')
			=> sprintf("the cast %s ==> %s", $this->targetType,
				substr($this->methodName, 2)),
			default => sprintf("method %s->%s", $this->targetType, $this->methodName)
		};
	}

}