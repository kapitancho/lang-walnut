<?php

namespace Walnut\Lang\Test\Almond\Engine;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NodeCodeMapper;
use Walnut\Lang\Almond\Runner\Blueprint\Cli\CliExecutionResult as CliExecutionResultInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Implementation\CliRunner;
use Walnut\Lang\Almond\Runner\Implementation\Compiler;
use Walnut\Lang\Almond\Source\Implementation\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\CallbackSourceFinder;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\PackageBasedSourceFinder;

class CodeExecutionTestHelper extends TestCase {

	protected CliRunner $cliRunner;
	protected Compiler $compiler;
	protected string $sourceCode = '';

	protected function setUp(): void {
		parent::setUp();

		$this->compiler = Compiler::builder()
			->withStartModule('test')
			->withCodeMapper(new NodeCodeMapper())
			->withAddedSourceFinder(new CallbackSourceFinder(
				[
					'test.nut' => fn() => $this->sourceCode,
				]
			))
			->withAddedSourceFinder(
				new PackageBasedSourceFinder(
					new PackageConfiguration(
						__DIR__ . '/../../../../../almond/walnut-src',
						[
							'core' => __DIR__ . '/../../../../../almond/core-nut-lib',
						]
					)
				)
			);

		$this->cliRunner = new CliRunner(
			$this->compiler,
		);
	}

	/** @return string|list<CompilationError[]> */
	protected function executeCodeSnippetRaw(
		string $code,
		string $typeDeclarations = '',
		string $valueDeclarations = '',
		array  $parameters = []
	): string|array {
		$this->sourceCode = "module test: $typeDeclarations => { $valueDeclarations myFn = ^Array<String> => Any :: { $code }; myFn(#)->printed; };";
		$result = $this->cliRunner->run($parameters);
		if ($result instanceof CliExecutionResultInterface) {
			return $result->returnValue;
		}
		return $result->errors;
	}

	protected function executeCodeSnippet(
		string $code,
		string $typeDeclarations = '',
		string $valueDeclarations = '',
		array  $parameters = []
	): string {
		$result = $this->executeCodeSnippetRaw(
			$code, $typeDeclarations, $valueDeclarations, $parameters
		);
		if (is_string($result)) {
			return $result;
		}
		$this->fail(
			'Unexpected compilation errors: ' . print_r($result, true)
		);
	}

	protected function executeErrorCodeSnippet(
		string $analyserMessage,
		string $code,
		string $typeDeclarations = '',
		string $valueDeclarations = '',
		array  $parameters = []
	): void {
		$result = $this->executeCodeSnippetRaw(
			$code, $typeDeclarations, $valueDeclarations, $parameters
		);
		if (is_array($result)) {
			self::assertStringContainsString($analyserMessage, $result[0]->errorMessage);
		} else {
			$this->fail(
				'No compilation error. The result was: ' . $result
			);
		}
	}

}