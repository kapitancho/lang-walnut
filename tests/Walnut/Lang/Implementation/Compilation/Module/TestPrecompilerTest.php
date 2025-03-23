<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Implementation\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Parser\Parser;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;
use Walnut\Lang\Implementation\Compilation\Module\ModuleImporter;

final class TestPrecompilerTest extends TestCase {

	public function testOk(): void {
		$result = new TestPrecompiler()->precompileSourceCode("modx", "source");
		$this->assertStringContainsString("~TestCases", $result);
	}

}