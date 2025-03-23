<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Cli;

interface CliEntryPoint {
	public function call(string $source, string ... $parameters): string;
}