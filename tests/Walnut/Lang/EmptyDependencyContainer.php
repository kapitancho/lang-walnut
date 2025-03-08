<?php

namespace Walnut\Lang\Test;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class EmptyDependencyContainer implements DependencyContainer {
	public function valueByType(Type $type): TypedValue|DependencyError {
		return new DependencyError(UnresolvableDependency::notFound, $type);
	}
}