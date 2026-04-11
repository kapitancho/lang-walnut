<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext as ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContextScope;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;
use WeakMap;

final class BacktraceValidationResultCollector implements ValidationContextScope, ValidationResultCollector {

	/** @var WeakMap<Expression|FunctionBody, ValidationContext> */
	private WeakMap $expressionChains;

	public function __construct() {
		$this->expressionChains = new WeakMap();
	}

	public function typeOf(Expression|FunctionBody $expression): Type|null {
		return $this->expressionChains[$expression]?->expressionType ?? null;
	}

	public function scopeAt(Expression|FunctionBody $expression): VariableScope|null {
		return $this->expressionChains[$expression]?->variableScope ?? null;
	}

	public function collect(ValidationContext $param): void {
		$chain = $this->collectExpressionChain();
		foreach($chain as $expression) {
			$this->expressionChains[$expression] = $param;
		}
	}

	private function collectExpressionChain(): array {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT, 100);
		$result = [];

		foreach ($trace as $frame) {
			if (!isset($frame['object'])) {
				continue;
			}

			$obj = $frame['object'];

			if ($obj instanceof Expression) {
				$result[] = $obj;
				continue;
			}

			if ($obj instanceof FunctionBody) {
				$result[] = $obj;
				break; // stop once we hit FunctionBody
			}
		}

		return $result;
	}

}