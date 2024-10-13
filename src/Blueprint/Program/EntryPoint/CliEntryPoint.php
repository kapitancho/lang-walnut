<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint;

interface CliEntryPoint {
	public function call(string $source, string ... $parameters): string;
}