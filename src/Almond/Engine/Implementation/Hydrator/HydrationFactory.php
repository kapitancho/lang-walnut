<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFactory as HydrationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest as HydrationRequestInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class HydrationFactory implements HydrationFactoryInterface {
	public function __construct(
		private ValueRegistry $valueRegistry,
		public NamedTypeHydrator $namedTypeHydrator,
		private string $initialPath
	) {}

	public function forValue(Value $value): HydrationRequestInterface {
		return new HydrationRequest(
			$this->valueRegistry,
			$this->namedTypeHydrator,
			$value,
			$this->initialPath
		);
	}

}