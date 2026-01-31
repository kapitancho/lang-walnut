<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface UserlandMethodStorage {
	public function addMethod(MethodName $name, UserlandMethod $method): UserlandMethod;
	public function addValidator(TypeName $name, UserlandFunction $validator): UserlandFunction;
}