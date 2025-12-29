<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiplyOk(): void {
		$result = $this->executeCodeSnippet('"hello " * 3;');
		$this->assertEquals('"hello hello hello "', $result);
	}

	public function testBinaryMultiplyInfinity(): void {
		$result = $this->executeCodeSnippet(
			'mul3("hello ");',
			valueDeclarations: "mul3 = ^str: Bytes => Bytes :: str * 3;"
		);
		$this->assertEquals('"hello hello hello "', $result);
	}

	public function testBinaryMultiplyZero(): void {
		$result = $this->executeCodeSnippet('"hello " * 0;');
		$this->assertEquals('""', $result);
	}

	public function testBinaryMultiplyInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello " * {-3};');
	}

	public function testBinaryMultiplyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello " * false;');
	}

}
