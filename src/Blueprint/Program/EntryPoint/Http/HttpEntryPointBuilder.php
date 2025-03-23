<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Http;

interface HttpEntryPointBuilder {
	public function build(string $source): SourceHttpEntryPoint;
}