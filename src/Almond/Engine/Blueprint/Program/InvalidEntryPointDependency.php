<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use InvalidArgumentException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyError;

final class InvalidEntryPointDependency extends InvalidArgumentException {
	private const string TypeIsNotDefined = "type is not defined";
	private const string ValueIsNotAFunction = "value is not a function";
	private const string DependencyCannotBeResolved = "dependency cannot be resolved";

    private function __construct(
	    public readonly TypeName $typeName,
	    public readonly string $failReason,
	    public readonly DependencyError|null $dependencyError = null
    ) {
        parent::__construct(
            sprintf(
                "Invalid entry point - type %s: %s",
                $typeName,
                $failReason,
            )
        );
    }

	public static function becauseTypeIsNotDefined(
		TypeName $typeName
	): never {
		throw new self(
			$typeName,
			self::TypeIsNotDefined
		);
	}

	public static function becauseValueIsNotAFunction(
		TypeName $typeName
	): never {
		throw new self(
			$typeName,
			self::ValueIsNotAFunction
		);
	}

	public static function becauseDependencyCannotBeResolved(
		TypeName $typeName,
		DependencyError $dependencyError
	): never {
		throw new self(
			$typeName,
			self::DependencyCannotBeResolved,
			$dependencyError
		);
	}

}