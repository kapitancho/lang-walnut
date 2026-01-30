<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;

interface CompilationFailureTransformer {
	public function fromParserException(ParserException $exception): CompilationFailure;
	public function fromModuleDependencyException(ModuleDependencyException $exception): CompilationFailure;
	public function fromPreBuildValidationFailure(
		PreBuildValidationFailure $failure,
		RootNode $rootNode
	): CompilationFailure;
	public function fromPostBuildValidationFailure(
		ValidationResult $failure,
		RootNode $rootNode
	): CompilationFailure;
}