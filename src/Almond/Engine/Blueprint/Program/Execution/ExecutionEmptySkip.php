<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Execution;

use Exception;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\EmptySkipTargetExpression;

final class ExecutionEmptySkip extends Exception {
	public function __construct(
		public readonly string $skipTargetId,
		public readonly ExecutionContext $executionContext
	) {
		parent::__construct();
	}
}