<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Optional;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanTypeNothing(): void {
		$result = $this->executeCodeSnippet("bool(empty);",
			valueDeclarations: 'bool = ^b: Optional<Nothing> => False :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeNull(): void {
		$result = $this->executeCodeSnippet("bool(null);",
			valueDeclarations: 'bool = ^b: Optional<Null> => False :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeOtherTrue(): void {
		$result = $this->executeCodeSnippet("bool('hi!');",
			valueDeclarations: 'bool = ^b: Optional<String> => Boolean :: b->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanTypeOtherFalse(): void {
		$result = $this->executeCodeSnippet("bool('');",
			valueDeclarations: 'bool = ^b: Optional<String> => Boolean :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeOtherPassEmpty(): void {
		$result = $this->executeCodeSnippet("bool(empty);",
			valueDeclarations: 'bool = ^b: Optional<String> => Boolean :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			'bool(empty)',
			valueDeclarations: 'bool = ^b: Optional => Boolean :: b->asBoolean(1);'
		);
	}

}