<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Hydrator\NamedTypeHydrator;

interface HydrationRequest {
	public ValueRegistry $valueRegistry { get; }
	public NamedTypeHydrator $namedTypeHydrator { get; }
	public Value $value { get; }

	public function ok(Value $value): HydrationSuccess;
	public function withError(string $message, Type $targetType): HydrationFailure;

	public function withAddedPathSegment(string $segment): HydrationRequest;
	public function forValue(Value $value): HydrationRequest;
}