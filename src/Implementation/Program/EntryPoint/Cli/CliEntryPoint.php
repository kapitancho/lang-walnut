<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Cli;

use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\CliEntryPoint as CliEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\CliEntryPointBuilder as CliEntryPointBuilderInterface;

final readonly class CliEntryPoint implements CliEntryPointInterface {
	public function __construct(
		private CliEntryPointBuilderInterface $cliEntryPointBuilder
	) {}

	public function call(string $source, string ... $parameters): string {
		return $this->cliEntryPointBuilder
			->build($source)
			->call(... $parameters);
	}
}