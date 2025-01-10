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

final class ModuleImporterTest extends TestCase {

	private ModuleImporter $moduleImporter;
	private ModuleLookupContext $moduleLookupContext;

	public function setUp(): void {
		parent::setUp();

		$this->moduleLookupContext = $this->createMock(ModuleLookupContext::class);
		$this->moduleImporter = new ModuleImporter(
			new WalexLexerAdapter(),
			$this->moduleLookupContext,
			new Parser(new TransitionLogger()),
			new NodeBuilderFactory(),
			new ModuleNodeBuilderFactory(),
		);
	}

	public function testImportOk(): void {
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core' => 'module core:',
				'test' => 'module test %% a, b:',
				'a' => 'module a %% c, d:',
				'b' => 'module b %% c:',
				'c' => 'module c:',
				'd' => 'module d:',
				default => ''
			});
		$modules = $this->moduleImporter->importModules('test');
		$this->assertCount(6, $modules->modules);
	}

	public function testImportLoop(): void {
		$this->expectException(ModuleDependencyException::class);
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core' => 'module core:',
				'test' => 'module test %% a:',
				'a' => 'module a %% test:',
				default => ''
			});
		$this->moduleImporter->importModules('test');
	}

	public function testImportModuleNotFound(): void {
		$this->expectException(ModuleDependencyException::class);
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core' => 'module core:',
				'test' => 'module test %% a:',
				default => throw new ModuleDependencyException($module)
			});
		$this->moduleImporter->importModules('test');
	}
}