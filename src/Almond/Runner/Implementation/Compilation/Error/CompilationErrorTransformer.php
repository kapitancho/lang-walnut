<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorTransformer as CompilationErrorTransformerInterface;

final readonly class CompilationErrorTransformer implements CompilationErrorTransformerInterface {
	public function __construct(
		private SourceNodeLocator $sourceLocator
	) {}

	public function fromParserException(ParserException $exception): CompilationError {
		return new ParserCompilationError($exception);
	}
	public function fromModuleDependencyException(ModuleDependencyException $exception): CompilationError {
		return new ModuleDependencyCompilationError($exception);
	}
	public function fromBuildException(BuildException $exception): CompilationError {
		return new BuildCompilationError($exception);
	}
	public function fromPreBuildValidationError(PreBuildValidationError $error): CompilationError {
		return new PreBuildCompilationError($error);
	}
	public function fromPostBuildValidationError(ValidationError $error): CompilationError {
		return new PostBuildCompilationError($error, $this->sourceLocator);
	}
}