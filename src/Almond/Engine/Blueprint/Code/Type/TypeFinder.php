<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface TypeFinder {

	/** @throws UnknownType */
	public function typeByName(TypeName $typeName): Type;
}