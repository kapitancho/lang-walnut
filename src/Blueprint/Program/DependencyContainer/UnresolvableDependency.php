<?php

namespace Walnut\Lang\Blueprint\Program\DependencyContainer;

enum UnresolvableDependency {
	case notFound;
	case circularDependency;
	case ambiguous;
	case unsupportedType;
	case errorWhileCreatingValue;

	public function errorInfo(): string {
		return match ($this) {
			UnresolvableDependency::notFound => "no appropriate value found",
			UnresolvableDependency::ambiguous => "ambiguity - multiple values found",
			UnresolvableDependency::circularDependency => "circular dependency detected",
			UnresolvableDependency::unsupportedType => "unsupported type found",
			UnresolvableDependency::errorWhileCreatingValue => 'error returned while creating value',
		};
	}
}