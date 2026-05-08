<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramSource as ProgramSourceInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class ProgramSource implements ProgramSourceInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private DependencyContainer $dependencyContainer,
		private ProgramValidator $programValidator,
	) {}

	public function asProgram(): ProgramInterface|ValidationResult {
		$validationResult = $this->programValidator->validateDependencies();
		if ($validationResult->hasErrors()) {
			return $validationResult;
		}
		return new Program(
			$this->typeRegistry,
			$this->dependencyContainer,
		);
	}

}