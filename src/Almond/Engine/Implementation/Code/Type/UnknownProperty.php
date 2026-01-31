<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty as UnknownPropertyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

final readonly class UnknownProperty implements UnknownPropertyInterface {
	public function __construct(
		public string|int $propertyName,
		public Type|Value $notFoundIn
	) {}
}