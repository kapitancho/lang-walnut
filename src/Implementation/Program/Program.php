<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Value\FunctionValue;

final readonly class Program implements ProgramInterface {

	public function __construct(
		private ProgramRegistry $programRegistry,
	) {}

	/** @throws InvalidEntryPointDependency */
	public function getEntryPoint(TypeNameIdentifier $typeName): ProgramEntryPoint {
		try {
			$value = $this->programRegistry->dependencyContainer->valueByType(
				$this->programRegistry->typeRegistry->typeByName($typeName)
			);
			if ($value instanceof DependencyError) {
				InvalidEntryPointDependency::becauseDependencyCannotBeResolved(
					$typeName,
					$value
				);
			}
			if ($value instanceof FunctionValue) {
				return new ProgramEntryPoint(
					$this->programRegistry,
					$value
				);
			}
			InvalidEntryPointDependency::becauseValueIsNotAFunction($typeName);
		} catch (UnknownType) {
			InvalidEntryPointDependency::becauseTypeIsNotDefined($typeName);
		}
	}

}