<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\True;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsInteger as AsIntegerInterface;

final readonly class AsInteger extends AsIntegerInterface {

	protected function getValidator(): callable {
		return fn(TrueType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->integerSubset([new Number(1)]);
	}

}
