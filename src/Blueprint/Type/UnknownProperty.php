<?php

namespace Walnut\Lang\Blueprint\Type;

use LogicException;

final class UnknownProperty extends LogicException {

	public function __construct(
		public string $propertyName,
		public string $notFoundIn
	) {
		parent::__construct(
			sprintf(
				"Unknown property %s of %s",
				$propertyName,
				$notFoundIn
			)
		);
	}
}