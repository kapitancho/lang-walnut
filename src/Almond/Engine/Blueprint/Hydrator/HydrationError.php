<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface HydrationError {
	public Type $targetType { get; }
	public string $message { get; }
	public string $path { get; }
}