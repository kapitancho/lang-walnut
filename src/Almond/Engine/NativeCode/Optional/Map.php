<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Optional;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias\OptionalProxy;

final readonly class Map extends OptionalProxy {

	protected function methodName(): MethodName {
		return new MethodName('map');
	}

}
