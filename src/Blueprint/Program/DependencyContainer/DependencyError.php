<?php

namespace Walnut\Lang\Blueprint\Program\DependencyContainer;

use Walnut\Lang\Blueprint\Type\Type;

final readonly class DependencyError {
	public function __construct(
		public UnresolvableDependency $unresolvableDependency,
		public Type $type,
		public mixed $metaData = null
	) {}
}