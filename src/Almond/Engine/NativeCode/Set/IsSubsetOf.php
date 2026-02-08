<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\SetSubset;

final readonly class IsSubsetOf extends SetSubset {

	protected function checkRelation(SetValue $target, SetValue $parameter): bool {
		return $this->isSubset($target, $parameter);
	}

}
