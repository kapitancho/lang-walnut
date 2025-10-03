<?php

namespace Walnut\Lang\Blueprint\Program;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;

final class InvalidEntryPointDependency extends InvalidArgumentException {
	private const string TypeIsNotDefined = "type is not defined";
	private const string ValueIsNotAFunction = "value is not a function";
	private const string DependencyCannotBeResolved = "dependency cannot be resolved";

    private function __construct(
	    public readonly TypeNameIdentifier $typeName,
	    public readonly string $failReason,
	    public readonly DependencyError|null $dependencyError = null
    ) {
        parent::__construct(
            sprintf(
                "Invalid entry point - type %s: %s%s",
                $typeName,
                $failReason,
                $dependencyError ? sprintf(" (%s)",
	                $dependencyError->unresolvableDependency->errorInfo()) : ""
            )
        );
    }

	public static function becauseTypeIsNotDefined(
		TypeNameIdentifier $typeName
	): never {
		throw new self(
			$typeName,
			self::TypeIsNotDefined
		);
	}

	public static function becauseValueIsNotAFunction(
		TypeNameIdentifier $typeName
	): never {
		throw new self(
			$typeName,
			self::ValueIsNotAFunction
		);
	}

	public static function becauseDependencyCannotBeResolved(
		TypeNameIdentifier $typeName,
		DependencyError $dependencyError
	): never {
		throw new self(
			$typeName,
			self::DependencyCannotBeResolved,
			$dependencyError
		);
	}

}