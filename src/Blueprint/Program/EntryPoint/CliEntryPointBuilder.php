<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint;

interface CliEntryPointBuilder {
	public function build(string $source): SourceCliEntryPoint;
}