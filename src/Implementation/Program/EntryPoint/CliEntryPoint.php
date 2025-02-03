<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\Compiler;
use Walnut\Lang\Blueprint\Program\EntryPoint\CliEntryPoint as CliEntryPointInterface;

final readonly class CliEntryPoint implements CliEntryPointInterface {
	public function __construct(
		private CliEntryPointBuilder $cliEntryPointBuilder
	) {}

	public function call(string $source, string ... $parameters): string {
		return $this->cliEntryPointBuilder
			->build($source)
			->call(... $parameters);
	}
}