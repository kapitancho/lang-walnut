<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramEntryPoint as ProgramEntryPointInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class ProgramEntryPoint implements ProgramEntryPointInterface {
	public function __construct(private FunctionValue $value) {}

	public function call(Value $parameter): Value {
		return $this->value->execute($parameter);
	}
}