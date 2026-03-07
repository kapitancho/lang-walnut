<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\False;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsInteger as AsIntegerInterface;

final readonly class AsInteger extends AsIntegerInterface {

	protected function getValidator(): callable {
		return fn(FalseType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->integerSubset([new Number(0)]);
	}

}
