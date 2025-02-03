<?php

namespace Walnut\Lang\Test\Implementation\Program\EntryPoint;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Compiler;
use Walnut\Lang\Implementation\Program\EntryPoint\CliEntryPoint;
use Walnut\Lang\Implementation\Program\EntryPoint\CliEntryPointBuilder;
use Walnut\Lang\Test\BaseProgramTestHelper;

class CliEntryPointTest extends BaseProgramTestHelper {

	public function setUp(): void {
		parent::setUp();
	}

	public function testCall(): void {
		$this->programContext->globalScopeBuilder->addVariable(
			new VariableNameIdentifier('main'),
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				$this->typeRegistry->nothing,
				$this->typeRegistry->integer(),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->integer(42)
					)
				)
			)
		);
		$moduleLookupContext = $this->createMock(ModuleLookupContext::class);
		$moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core' => 'module core: Global = :[];',
				'test' => 'module test: main = ^Array<String> => String :: #->printed;',
				default => ''
			});

		$compiler = new Compiler($moduleLookupContext);
		$cliEntryPoint = new CliEntryPoint(new CliEntryPointBuilder($compiler));
		$result = $cliEntryPoint->call('test', 'Hello, World!');
		$this->assertEquals("['Hello, World!']", $result);
	}

}