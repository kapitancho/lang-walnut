<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\True;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\UnaryBitwiseNot as UnaryBitwiseNotInterface;

final readonly class UnaryBitwiseNot extends UnaryBitwiseNotInterface {

	protected function getValidator(): callable {
		return fn(TrueType $targetType, NullType $parameterType): FalseType =>
			$this->typeRegistry->false;
	}

}
