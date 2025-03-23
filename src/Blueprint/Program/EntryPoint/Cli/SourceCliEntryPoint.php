<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Cli;

interface SourceCliEntryPoint {
	public function call(string ... $parameters): string;
}