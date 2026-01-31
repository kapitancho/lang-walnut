<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure as HydrationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest as HydrationRequestInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess as HydrationSuccessInterface;

final readonly class HydrationRequest implements HydrationRequestInterface {

	public function __construct(
		public ValueRegistry $valueRegistry,
		public NamedTypeHydrator $namedTypeHydrator,
		public Value $value,
		private string $path
	) {}

	public function ok(Value $value): HydrationSuccessInterface {
		return new HydrationSuccess($value);
	}

	public function withError(string $message, Type $targetType): HydrationFailureInterface {
		return new HydrationFailure(
			[new HydrationError($targetType, $message, $this->path)]
		);
	}

	public function withAddedPathSegment(string $segment): HydrationRequestInterface {
		return clone($this, ['path' => $this->path . $segment]);
	}

	public function forValue(Value $value): HydrationRequestInterface {
		return clone($this, ['value' => $value]);
	}

}