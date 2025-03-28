<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\InvalidEntryPoint;
use Walnut\Lang\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;

final readonly class Program implements ProgramInterface {

	public function __construct(
		private ProgramRegistry $programRegistry,
	) {}

	/** @throws InvalidEntryPoint */
	public function getEntryPoint(
		VariableNameIdentifier $functionName,
		Type $expectedParameterType,
		Type $expectedReturnType
	): ProgramEntryPoint {
		$typedValue = $this->programRegistry->globalScope->findTypedValueOf($functionName);

		if($typedValue === UnknownVariable::value) {
			InvalidEntryPoint::becauseFunctionIsNotDefined(
				$functionName, $expectedParameterType, $expectedReturnType
			);
		}
		$type = $typedValue->type;
		$value = $typedValue;
		if (!($type instanceof FunctionType) || !($value instanceof FunctionValue)) {
			InvalidEntryPoint::becauseValueIsNotAFunction(
				$functionName, $expectedParameterType, $expectedReturnType
			);
		}
		if (!$expectedParameterType->isSubtypeOf($type->parameterType)) {
			InvalidEntryPoint::becauseWrongParameterType(
				$functionName,
				$expectedParameterType,
				$expectedReturnType,
				$type->parameterType
			);
		}
		if (!$type->returnType->isSubtypeOf($expectedReturnType)) {
			InvalidEntryPoint::becauseWrongReturnType(
				$functionName, $expectedParameterType, $expectedReturnType
			);
		}
		return new ProgramEntryPoint(
			$this->programRegistry,
			$this->programRegistry->globalScope,
			$value->withVariableValueScope($this->programRegistry->globalScope)
		);
	}
}