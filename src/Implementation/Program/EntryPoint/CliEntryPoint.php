<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\Compiler;
use Walnut\Lang\Blueprint\Program\EntryPoint\CliEntryPoint as CliEntryPointInterface;

final readonly class CliEntryPoint implements CliEntryPointInterface {
	public function __construct(
		private Compiler $compiler
	) {}

	public function call(string $source, string ... $parameters): string {
		$compilationResult = $this->compiler->compile($source);
		$program = $compilationResult->program;
		$tr = $compilationResult->compilationContext->typeRegistry;
		$vr = $compilationResult->compilationContext->valueRegistry;
		$ep = $program->getEntryPoint(
			new VariableNameIdentifier('main'),
			$tr->array($tr->string()),
			$tr->string()
		);
		return $ep->call($vr->tuple(
			array_map(fn(string $arg) => $vr->string($arg), $parameters)
		))->literalValue;
	}
}