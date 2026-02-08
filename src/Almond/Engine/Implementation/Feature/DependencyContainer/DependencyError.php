<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainerErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyError as DependencyErrorInterface;

final readonly class DependencyError implements DependencyErrorInterface {
	public function __construct(
		public DependencyContainerErrorType $errorType,
		public Type $type,
		public string|null $message = null
	) {}
}