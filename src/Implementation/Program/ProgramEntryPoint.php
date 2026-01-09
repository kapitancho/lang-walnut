<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Program\ProgramEntryPoint as ProgramEntryPointInterface;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;

final readonly class ProgramEntryPoint implements ProgramEntryPointInterface {
	public function __construct(
		private ProgramRegistry $programRegistry,
		private FunctionValue $value
	) {}

	public function call(Value $parameter): Value {
		return $this->value->execute(
			$this->programRegistry->executionContext,
			$parameter
		);
	}
}