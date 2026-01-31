<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramEntryPoint as ProgramEntryPointInterface;

final readonly class ProgramEntryPoint implements ProgramEntryPointInterface {
	public function __construct(private FunctionValue $value) {}

	public function call(Value $parameter): Value {
		return $this->value->execute($parameter);
	}
}