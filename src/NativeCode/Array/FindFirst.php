<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayFindFirstIFindLast;

final readonly class FindFirst implements NativeMethod {
	use ArrayFindFirstIFindLast;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof TupleValue && $parameter instanceof FunctionValue) {
			$values = $target->values;
			$true = $programRegistry->valueRegistry->true;
			foreach($values as $value) {
				$r = $parameter->execute($programRegistry->executionContext, $value);
				if ($true->equals($r)) {
					return $value;
				}
			}
			return $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->core->itemNotFound
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}