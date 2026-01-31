<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationError as HydrationErrorInterface;

final readonly class HydrationError implements HydrationErrorInterface {
	public function __construct(
		public Type $targetType,
		public string $message,
		public string $path
	) {}
}