<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty as UnknownPropertyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class UnknownProperty implements UnknownPropertyInterface {
	public function __construct(
		public string|int $propertyName,
		public Type|Value $notFoundIn
	) {}
}