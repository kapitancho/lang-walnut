<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;

interface UserlandMethodRegistry {
	/** @return UserlandMethod[] */
	public function methodsByName(MethodName $methodName): array;

	/** @return array<string, list<UserlandMethod>> */
	public function allMethods(): array;
}