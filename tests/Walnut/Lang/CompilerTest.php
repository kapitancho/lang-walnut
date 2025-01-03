<?php

namespace Walnut\Lang;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CompilationResult;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Compilation\Compiler;
use Walnut\Lang\Implementation\Compilation\FolderBasedModuleLookupContext;

final class CompilerTest extends TestCase {
	private const PATH = __DIR__ . '/../../../core-nut-lib';

	private Compiler $compiler;

	protected function setUp(): void {
		parent::setUp();

		$this->compiler = new Compiler(
			new FolderBasedModuleLookupContext(self::PATH)
		);

	}

	#[DataProvider('sources')]
	public function testCompilation(string $source): void {
		try {
			$compilationResult = $this->compiler->compile($source);
			$this->assertInstanceOf(CompilationResult::class, $compilationResult);

			$isExecutable = preg_match('/^(cast\d+|demo-\w+|nwk-\w+|lang-[\w\-]+)$/', $source);
			if ($isExecutable) {
				$program = $compilationResult->program;
				$tr = $compilationResult->programRegistry->typeRegistry;
				$vr = $compilationResult->programRegistry->valueRegistry;
				$ep = $program->getEntryPoint(
					new VariableNameIdentifier('main'),
					$tr->array($tr->string()),
					$tr->string()
				);
				$value = $ep->call($vr->tuple([]));
				$this->assertInstanceOf(Value::class, $value);

				$this->assertNotEquals('{}', json_encode($compilationResult->programRegistry));
				$this->assertNotEquals('', (string)$compilationResult->programRegistry);
			}
		} catch (AnalyserException $e) {
			$this->assertStringContainsString('cannot be resolved', $e->getMessage());
		}
	}

	public static function sources(): iterable {
		$sourceRoot = self::PATH;
		foreach(glob("$sourceRoot/*.nut") as $sourceFile) {
			yield [str_replace('.nut', '', basename($sourceFile))];
		}
	}
}