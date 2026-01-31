<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationError;

interface CompilationErrorTransformer {
	public function fromParserException(ParserException $exception): CompilationError;
	public function fromModuleDependencyException(ModuleDependencyException $exception): CompilationError;
	public function fromBuildException(BuildException $exception): CompilationError;
	public function fromPreBuildValidationError(PreBuildValidationError $error): CompilationError;
	public function fromPostBuildValidationError(ValidationError $error): CompilationError;
}