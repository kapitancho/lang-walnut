<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyError;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue;

final readonly class Program implements ProgramInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private DependencyContainer $dependencyContainer,
	) {}

	/** @throws InvalidEntryPointDependency */
	public function getEntryPoint(TypeName $typeName): ProgramEntryPoint {
		$type = $this->typeRegistry->typeByName($typeName);
		if ($type === UnknownType::value) {
			InvalidEntryPointDependency::becauseTypeIsNotDefined($typeName);
		}
		$value = $this->dependencyContainer->valueForType($type);
		if ($value instanceof DependencyError) {
			InvalidEntryPointDependency::becauseDependencyCannotBeResolved(
				$typeName,
				$value
			);
		}
		if (!($value instanceof FunctionValue)) {
			InvalidEntryPointDependency::becauseValueIsNotAFunction($typeName);
		}
		return new ProgramEntryPoint($value);
	}
}