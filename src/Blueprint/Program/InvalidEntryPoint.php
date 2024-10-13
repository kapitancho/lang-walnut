<?php

namespace Walnut\Lang\Blueprint\Program;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

final class InvalidEntryPoint extends InvalidArgumentException {
	private const FunctionIsNotDefined = "function is not defined";
	private const ValueIsNotAFunction = "value is not a function";
	private const WrongParameterType = "wrong parameter type: %s expected, %s given";
	private const WrongReturnType = "wrong return type";

    private function __construct(
	    public readonly VariableNameIdentifier $functionName,
	    public readonly Type $expectedParameterType,
	    public readonly Type $expectedReturnType,
	    public readonly string $failReason
    ) {
        parent::__construct(
            sprintf(
                "Invalid entry point %s: %s",
                $this->functionName,
                $failReason
            )
        );
    }

	public static function becauseFunctionIsNotDefined(
		VariableNameIdentifier $functionName, Type $expectedParameterType, Type $expectedReturnType,
	): never {
		throw new self(
			$functionName,
			$expectedParameterType,
			$expectedReturnType,
			self::FunctionIsNotDefined
		);
	}

	public static function becauseValueIsNotAFunction(
		VariableNameIdentifier $functionName, Type $expectedParameterType, Type $expectedReturnType,
	): never {
		throw new self(
			$functionName,
			$expectedParameterType,
			$expectedReturnType,
			self::ValueIsNotAFunction
		);
	}

	public static function becauseWrongParameterType(
		VariableNameIdentifier $functionName,
		Type $expectedParameterType,
		Type $expectedReturnType,
		Type $actualParameterType
	): never {
		throw new self(
			$functionName,
			$expectedParameterType,
			$expectedReturnType,
			sprintf(self::WrongParameterType, $expectedParameterType, $actualParameterType)
		);
	}

	public static function becauseWrongReturnType(
		VariableNameIdentifier $functionName, Type $expectedParameterType, Type $expectedReturnType,
	): never {
		throw new self(
			$functionName,
			$expectedParameterType,
			$expectedReturnType,
			self::WrongReturnType
		);
	}
}