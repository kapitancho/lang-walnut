<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;

interface UserlandMethodStorage {
	public function addMethod(MethodName $name, UserlandMethod $method): UserlandMethod;
}