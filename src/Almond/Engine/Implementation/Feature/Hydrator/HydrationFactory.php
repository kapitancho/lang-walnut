<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFactory as HydrationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest as HydrationRequestInterface;

final readonly class HydrationFactory implements HydrationFactoryInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		public NamedTypeHydrator $namedTypeHydrator,
		private string $initialPath
	) {}

	public function forValue(Value $value): HydrationRequestInterface {
		return new HydrationRequest(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->namedTypeHydrator,
			$value,
			$this->initialPath
		);
	}

}