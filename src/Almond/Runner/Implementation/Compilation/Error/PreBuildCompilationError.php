<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationError;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;

final class PreBuildCompilationError implements CompilationError {

	public function __construct(private readonly PreBuildValidationError $preBuildValidationError) {}

	public CompilationErrorType $errorType {
		get => match($this->preBuildValidationError->errorType) {
			PreBuildValidationErrorType::invalidRange => CompilationErrorType::invalidRange,
			PreBuildValidationErrorType::nonUniqueValueDefinition => CompilationErrorType::nonUniqueValueDefinition,
			PreBuildValidationErrorType::missingType => CompilationErrorType::missingType,
			PreBuildValidationErrorType::missingValue => CompilationErrorType::missingValue,
			default => CompilationErrorType::other
		};
	}

	public string $errorMessage {
		get => $this->preBuildValidationError->errorMessage;
	}

	/** @var list<SourceLocation> */
	public array $sourceLocations {
		get => array_map(
			fn(SourceNode $node): SourceLocation => $node->sourceLocation,
			$this->preBuildValidationError->sourceNodes
		);
	}
}