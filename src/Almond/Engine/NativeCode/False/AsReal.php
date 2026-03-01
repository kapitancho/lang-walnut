<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\False;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsReal as AsRealInterface;

final readonly class AsReal extends AsRealInterface {

	protected function getValidator(): callable {
		return fn(FalseType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->realSubset([new Number(0)]);
	}

}
