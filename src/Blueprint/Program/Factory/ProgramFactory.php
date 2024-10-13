<?php

namespace Walnut\Lang\Blueprint\Program\Factory;

use Walnut\Lang\Blueprint\Compilation\CodeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

interface ProgramFactory {
	public function codeBuilder(): CodeBuilder;
	public function builder(): ProgramBuilder;
	public function registry(): ProgramRegistry;
}