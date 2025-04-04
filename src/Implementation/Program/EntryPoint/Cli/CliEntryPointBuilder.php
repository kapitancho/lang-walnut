<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Cli;

use Walnut\Lang\Blueprint\Compilation\Compiler;
use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\CliEntryPointBuilder as CliEntryPointBuilderInterface;

final readonly class CliEntryPointBuilder implements CliEntryPointBuilderInterface {
	public function __construct(
		private Compiler $compiler
	) {}

	public function build(string $source): SourceCliEntryPoint {
		return new SourceCliEntryPoint(
			$this->compiler->compile($source)
		);
	}
}