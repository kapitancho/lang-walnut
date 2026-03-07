<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NamedNativeMethod;

/** @extends NamedNativeMethod<SealedType, Type, SealedValue, Value> */
abstract readonly class FileMethod extends NamedNativeMethod {

	protected function getExpectedTypeName(): TypeName {
		return new TypeName('File');
	}

}