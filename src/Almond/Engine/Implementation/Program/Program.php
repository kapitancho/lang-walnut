<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyError;
use Walnut\Lang\Almond\Engine\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program as ProgramInterface;

final readonly class Program implements ProgramInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private DependencyContainer $dependencyContainer,
	) {}

	/** @throws InvalidEntryPointDependency */
	public function getEntryPoint(TypeName $typeName): ProgramEntryPoint {
		try {
			$type = $this->typeRegistry->typeByName($typeName);
		} catch (UnknownType) {
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