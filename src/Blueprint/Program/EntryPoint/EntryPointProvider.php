<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint;

use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

interface EntryPointProvider {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }

	public Program $program { get; }
}