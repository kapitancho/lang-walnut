<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer;

enum DependencyContainerErrorType {
	case notFound;
	case circularDependency;
	case unsupportedType;
	case errorWhileCreatingValue;
	case runtimeError;

	public function errorInfo(): string {
		return match ($this) {
			DependencyContainerErrorType::notFound => "no appropriate value found",
			DependencyContainerErrorType::circularDependency => "circular dependency detected",
			DependencyContainerErrorType::unsupportedType => "unsupported type found",
			DependencyContainerErrorType::errorWhileCreatingValue => 'error returned while creating value',
			DependencyContainerErrorType::runtimeError => 'runtime error occurred while creating value',
		};
	}
}