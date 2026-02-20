<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class IsSubtypeOfTest extends CodeExecutionTestHelper {

	public function testIsSubtypeOf(): void {
		$result = $this->executeCodeSnippet("type{Integer}->isSubtypeOf(type{Real});");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfArrayTuple(): void {
		$result = $this->executeCodeSnippet("type{Array<Integer, 2>}->isSubtypeOf(type[Integer, String]);");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfMapRecord(): void {
		$result = $this->executeCodeSnippet("type{Map<'a'|'b':Integer, 2>}->isSubtypeOf(type[a: Integer, b: String]);");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfMetaTypeNamed(): void {
		$result = $this->executeCodeSnippet("type{Atom}->isSubtypeOf(type{Named});");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfMetaTypeNamedFalse(): void {
		$result = $this->executeCodeSnippet("type{Named}->isSubtypeOf(type{Atom});");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfMetaTypeEnumeration(): void {
		$result = $this->executeCodeSnippet("type{EnumerationSubset}->isSubtypeOf(type{Enumeration});");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "type{Integer}->isSubtypeOf(3.14);");
	}

}