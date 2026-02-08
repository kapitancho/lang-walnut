<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface DependencyError {
	public DependencyContainerErrorType $errorType { get; }
	public Type $type { get; }
	public string|null $message { get; }
}