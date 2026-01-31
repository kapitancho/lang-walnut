<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface HydrationError {
	public Type $targetType { get; }
	public string $message { get; }
	public string $path { get; }
}