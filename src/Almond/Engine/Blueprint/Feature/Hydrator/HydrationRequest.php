<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;

interface HydrationRequest {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }
	public NamedTypeHydrator $namedTypeHydrator { get; }
	public Value $value { get; }

	public function ok(Value $value): HydrationSuccess;
	public function withError(string $message, Type $targetType): HydrationFailure;

	public function withAddedPathSegment(string $segment): HydrationRequest;
	public function forValue(Value $value): HydrationRequest;
}