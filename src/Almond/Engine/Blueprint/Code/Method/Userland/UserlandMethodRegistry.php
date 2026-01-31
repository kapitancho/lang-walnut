<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

interface UserlandMethodRegistry {
	/** @return UserlandMethod[] */
	public function methodsByName(MethodName $methodName): array;

	/** @return UserlandMethod */
	public function allMethods(): array;

	/** @return array<string, UserlandFunction> */
	public function allValidators(): array;
}