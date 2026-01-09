<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayIndexOfLastIndexOf;

final readonly class IndexOf implements NativeMethod {
	use ArrayIndexOfLastIndexOf;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof TupleValue) {
			$values = $target->values;
			foreach ($values as $index => $value) {
				if ($value->equals($parameter)) {
					return $programRegistry->valueRegistry->integer($index);
				}
			}
			return $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->core->itemNotFound,
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}