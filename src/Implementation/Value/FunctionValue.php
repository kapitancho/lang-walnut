<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Value\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class FunctionValue implements FunctionValueInterface, JsonSerializable {

	private function __construct(
		private TypeRegistry                     $typeRegistry,
		public FunctionType $type,
		public UserlandFunction                 $function,
		public VariableValueScopeInterface|null $variableValueScope,
		public VariableNameIdentifier|null      $selfReferAs
	) {}

	public static function of(
		TypeRegistry                    $typeRegistry,
		UserlandFunction                 $function,
	): self {
		return new self(
			$typeRegistry,
			$typeRegistry->function(
				$function->parameter->type,
				$function->returnType
			),
			$function,
			null,
			null
		);
	}

	public function withVariableValueScope(VariableValueScopeInterface $variableValueScope): self {
		return new self(
			$this->typeRegistry,
			$this->type,
			$this->function,
			$variableValueScope,
			$this->selfReferAs
		);
	}

	public function withSelfReferenceAs(VariableNameIdentifier $variableName): self {
		return new self(
			$this->typeRegistry,
			$this->type,
			$this->function,
			$this->variableValueScope,
			$variableName
		);
	}

	private function fillAnalyserContext(AnalyserContext $analyserContext): AnalyserContext {
		foreach ($this->variableValueScope?->allTypes() ?? [] as $variableName => $type) {
			/** @noinspection PhpParamsInspection */ //PhpStorm bug
			$analyserContext = $analyserContext->withAddedVariableType($variableName, $type);
		}
		if ($this->selfReferAs) {
			$analyserContext = $analyserContext->withAddedVariableType(
				$this->selfReferAs,
				$this->type
			);
		}
		return $analyserContext;
	}

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		$this->function->selfAnalyse(
			$this->fillAnalyserContext($analyserContext)
		);
	}

	public function execute(ExecutionContext $executionContext, Value $parameterValue): Value {
		foreach ($this->variableValueScope?->allTypedValues() ?? [] as $variableName => $v) {
			/** @noinspection PhpParamsInspection */ //PhpStorm bug
			$executionContext = $executionContext->withAddedVariableValue($variableName, $v);
		}
		if ($this->selfReferAs) {
			$executionContext = $executionContext->withAddedVariableValue(
				$this->selfReferAs,
				$this
			);
		}
		return $this->function->execute($executionContext, null, $parameterValue);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		$analyseErrors = [];
		$d = $this->function->dependency->type;
		if (!($d instanceof NothingType)) {
			$value = $dependencyContainer->valueByType($d);
			if ($value instanceof DependencyError) {
				$analyseErrors[] = sprintf("Error in %s: the dependency %s cannot be resolved: %s (type: %s)",
					$this->function->displayName,
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

	public function equals(Value $other): bool {
		return $other instanceof self && (string)$this === (string)$other;
	}

	public function composeWith(FunctionValueInterface $nextFunction): FunctionValueInterface {
		return new CompositeFunctionValue(
			$this->typeRegistry,
			$this,
			$nextFunction
		);
	}


	public function __toString(): string {
		$dep = $this->function->dependency->type instanceof NothingType ?
			'' : '%% ' . sprintf("%s ", $this->function->dependency);
		return sprintf(
			"^%s => %s %s:: %s",
			$this->function->parameter,
			$this->function->returnType,
			$dep,
			$this->function->functionBody
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Function',
			'parameter' => $this->function->parameter,
			'dependency' => $this->function->dependency,
			'returnType' => $this->function->returnType,
			'body' => $this->function->functionBody
		];
	}
}