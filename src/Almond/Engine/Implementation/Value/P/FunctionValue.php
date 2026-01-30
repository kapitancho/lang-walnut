<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope;

final readonly class FunctionValue implements FunctionValueInterface {
	public Type $type;

	public function __construct(
		private TypeRegistry $typeRegistry,

		public UserlandFunction $function,
		public VariableValueScope $variableValueScope,
		public VariableName|null $selfReferAs
	) {
		$this->type = $this->typeRegistry->function(
			$this->function->parameter->type,
			$this->function->returnType
		);
	}

	public function withSelfReferenceAs(VariableName $variableName): self {
		return clone($this, ['selfReferAs' => $variableName]);
	}
	public function withVariableValueScope(VariableValueScope $variableValueScope): self {
		return clone($this, ['variableValueScope' => $variableValueScope]);
	}

	public function equals(Value $other): bool {
		return $other instanceof FunctionValueInterface && (string)$this === (string)$other;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$request = $request->ok();
		$step = $this->function->validateInVariableScope(
			$request instanceof ValidationContext ?
				$request->variableScope : $this->scope());
		if ($step instanceof ValidationFailure) {
			$request = $request->mergeFailure($step);
		}
		return $request;
		//$request = $this->function->parameter->type->validate($request);
		//return $this->function->returnType->validate($request);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->function->validateDependencies($dependencyContext);
	}

	private function scope(): VariableValueScope {
		$scope = $this->variableValueScope;
		if ($this->selfReferAs) {
			$scope = $scope->withAddedVariableValue($this->selfReferAs, $this);
		}
		return $scope;
	}

	/** @throws ExecutionException */
	public function execute(Value $value): Value {
		return $this->function->execute($this->scope(), null, $value);
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