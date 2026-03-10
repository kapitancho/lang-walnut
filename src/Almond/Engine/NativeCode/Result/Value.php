<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Result;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias\ResultProxy;

final readonly class Value extends ResultProxy {

	protected function methodName(): MethodName {
		return new MethodName('value');
	}

}
