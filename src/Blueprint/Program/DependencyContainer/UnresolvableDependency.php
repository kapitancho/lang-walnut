<?php

namespace Walnut\Lang\Blueprint\Program\DependencyContainer;

enum UnresolvableDependency {
	case notFound;
	case circularDependency;
	case ambiguous;
	case unsupportedType;
	case errorWhileCreatingValue;
}