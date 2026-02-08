<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;

final class PostBuildCompilationError implements CompilationError {

	public function __construct(
		private readonly ValidationError $validationError,
		private readonly SourceNodeLocator $sourceLocator
	) {}

	public CompilationErrorType $errorType {
		get => match($this->validationError->type) {
			ValidationErrorType::typeTypeMismatch => CompilationErrorType::typeTypeMismatch,
			ValidationErrorType::valueTypeMismatch => CompilationErrorType::valueTypeMismatch,
			ValidationErrorType::mutableTypeMismatch => CompilationErrorType::mutableTypeMismatch,
			ValidationErrorType::undefinedVariable => CompilationErrorType::undefinedVariable,
			ValidationErrorType::mapKeyTypeMismatch => CompilationErrorType::mapKeyTypeMismatch,
			ValidationErrorType::shapeMismatch => CompilationErrorType::shapeMismatch,
			ValidationErrorType::undefinedMethod => CompilationErrorType::undefinedMethod,
			ValidationErrorType::invalidTargetType => CompilationErrorType::invalidTargetType,
			ValidationErrorType::invalidParameterType => CompilationErrorType::invalidParameterType,
			ValidationErrorType::invalidReturnType => CompilationErrorType::invalidReturnType,
			ValidationErrorType::dependencyNotFound => CompilationErrorType::dependencyNotFound,
			ValidationErrorType::variableScopeMismatch => CompilationErrorType::variableScopeMismatch,
			ValidationErrorType::userlandMethodMismatch => CompilationErrorType::userlandMethodMismatch,
			default => CompilationErrorType::other
		};
	}

	public string $errorMessage {
		get => $this->validationError->message;
	}

	/** @var list<SourceLocation> */
	public array $sourceLocations {
		get {
			$sourceNode = $this->validationError->origin ?
				$this->sourceLocator->getSourceNode($this->validationError->origin) : null;
			return $sourceNode ? [$sourceNode->sourceLocation] : [];
		}
	}
}