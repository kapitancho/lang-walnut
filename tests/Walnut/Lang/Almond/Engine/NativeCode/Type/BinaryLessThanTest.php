<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryLessThanTest extends CodeExecutionTestHelper {

	public function testIsSubtypeOf(): void {
		$result = $this->executeCodeSnippet("{`Integer} < `Real;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfRange1(): void {
		$result = $this->executeCodeSnippet("`Integer<2..10> < `Integer<1..11>;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfRange2(): void {
		$result = $this->executeCodeSnippet("`Integer<2..10> < `Integer<2..10>;");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfRange3(): void {
		$result = $this->executeCodeSnippet("`Integer<2..10> < `Integer<3..9>;");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfMetaTypeNamed(): void {
		$result = $this->executeCodeSnippet("`Atom < `Named;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfMetaTypeNamedFalse(): void {
		$result = $this->executeCodeSnippet("`Named < `Atom;");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfMetaTypeEnumeration(): void {
		$result = $this->executeCodeSnippet("`EnumerationSubset < `Enumeration;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "{`Integer} < 3.14;");
	}

}