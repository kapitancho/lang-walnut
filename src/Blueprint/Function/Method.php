<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Type\Type;

interface Method extends MethodDraft {
	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue;
}