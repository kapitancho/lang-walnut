<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NamedNativeMethod;

abstract readonly class FileMethod extends NamedNativeMethod {

	protected function getExpectedTypeName(): TypeName {
		return new TypeName('File');
	}

}