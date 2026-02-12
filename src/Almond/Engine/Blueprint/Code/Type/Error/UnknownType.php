<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

final class UnknownType extends EngineException {
	public function __construct(
		public readonly TypeName $typeName,
	) {
		parent::__construct(
			sprintf(
				'The type "%s" does not exist.',
				$this->typeName
			)
		);
	}

	public static function of(TypeName $typeName): never {
		throw new self($typeName);
	}
}