<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Cli;

interface CliEntryPointBuilder {
	public function build(string $source): SourceCliEntryPoint;
}