<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

final class TypeAlreadyDefined extends EngineException {
	public function __construct(
		public readonly TypeName $typeName
	) {
		parent::__construct(
			sprintf(
				'The type with the name "%s" is already defined and cannot be re-defined.',
				$this->typeName
			)
		);
	}

	public static function of(TypeName $typeName): never {
		throw new self($typeName);
	}
}