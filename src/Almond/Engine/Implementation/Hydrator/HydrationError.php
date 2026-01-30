<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationError as HydrationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

final readonly class HydrationError implements HydrationErrorInterface {
	public function __construct(
		public Type $targetType,
		public string $message,
		public string $path
	) {}
}