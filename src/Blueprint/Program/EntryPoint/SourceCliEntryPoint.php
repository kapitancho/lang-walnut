<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint;

interface SourceCliEntryPoint {
	public function call(string ... $parameters): string;
}