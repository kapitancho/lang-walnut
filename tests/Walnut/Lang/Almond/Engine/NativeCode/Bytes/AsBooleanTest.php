<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanEmpty(): void {
		$result = $this->executeCodeSnippet('""->asBoolean;');
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanNotEmpty(): void {
		$result = $this->executeCodeSnippet('"hello"->asBoolean;');
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanCast(): void {
		$result = $this->executeCodeSnippet('"hello"->as(`Boolean);');
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet('bool("hello");',
			valueDeclarations: 'bool = ^a: Bytes => Boolean :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanTypeEmpty(): void {
		$result = $this->executeCodeSnippet('bool("");',
			valueDeclarations: 'bool = ^a: Bytes<0> => False :: a->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeNotEmpty(): void {
		$result = $this->executeCodeSnippet('bool("hello");',
			valueDeclarations: 'bool = ^a: Bytes<1..> => True :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '""->asBoolean(5);');
	}

}
