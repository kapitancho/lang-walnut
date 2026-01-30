<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;

interface NamespaceConfigMap {
	public function getNamespaceFor(TypeName $type): string;
}