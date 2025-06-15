<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Type\DataType;

interface DataValue extends Value {
	public DataType $type { get; }
	public Value $value { get; }
}