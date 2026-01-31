<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface NamespaceConfigMap {
	public function getNamespaceFor(TypeName $type): string;
}