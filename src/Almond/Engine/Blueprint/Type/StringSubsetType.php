<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;

interface StringSubsetType extends StringType {
	/** @var list<string> */
	public array $subsetValues { get; }
	public LengthRange $range { get; }

	public function contains(string $value): bool;
}
