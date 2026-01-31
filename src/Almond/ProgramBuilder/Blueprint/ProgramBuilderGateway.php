<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;

interface ProgramBuilderGateway {

	/** @throws BuildException */
	public function build(RootNode $source, ProgramContext $target, CodeMapper $codeMapper): void;
}