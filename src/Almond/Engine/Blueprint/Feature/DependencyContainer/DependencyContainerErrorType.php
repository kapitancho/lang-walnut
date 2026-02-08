<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer;

enum DependencyContainerErrorType {
	case notFound;
	case circularDependency;
	case ambiguous;
	case unsupportedType;
	case errorWhileCreatingValue;

	public function errorInfo(): string {
		return match ($this) {
			DependencyContainerErrorType::notFound => "no appropriate value found",
			DependencyContainerErrorType::ambiguous => "ambiguity - multiple values found",
			DependencyContainerErrorType::circularDependency => "circular dependency detected",
			DependencyContainerErrorType::unsupportedType => "unsupported type found",
			DependencyContainerErrorType::errorWhileCreatingValue => 'error returned while creating value',
		};
	}
}