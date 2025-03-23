<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint;

use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider as EntryPointProviderInterface;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

final readonly class EntryPointProvider implements EntryPointProviderInterface {

	public function __construct(
		public TypeRegistry $typeRegistry,
		public ValueRegistry $valueRegistry,
		public Program $program
	) {}

}