<?php

namespace Walnut\Lang\Blueprint\Program\Factory;

use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\Compilation\CodeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

interface ProgramFactory {
	public NodeBuilderFactory $nodeBuilderFactory { get; }
	public CodeBuilder $codeBuilder { get; }
	public ProgramBuilder $builder { get; }
	public ProgramRegistry $registry { get; }
}