<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Program\ProgramEntryPoint as ProgramEntryPointInterface;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;

final readonly class ProgramEntryPoint implements ProgramEntryPointInterface {
	public function __construct(
		private VariableValueScope $globalScope,
		private FunctionValue $value
	) {}

	public function call(Value $parameter): Value {
		return $this->value->execute(
			new ExecutionContext($this->globalScope),
			$parameter
		);
	}
}