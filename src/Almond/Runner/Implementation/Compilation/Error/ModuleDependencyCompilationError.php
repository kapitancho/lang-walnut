<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;

final class ModuleDependencyCompilationError implements CompilationError {

	public function __construct(
		private readonly ModuleDependencyException $moduleDependencyException
	) {}

	public CompilationErrorType $errorType {
		get => $this->moduleDependencyException->isLoop ?
			CompilationErrorType::moduleDependencyLoop :
			CompilationErrorType::moduleDependencyMissing;
	}

	public string $errorMessage {
		get => $this->moduleDependencyException->getMessage();
	}

	/** @var list<SourceLocation> */
	public array $sourceLocations {
		get => [];
	}
}