<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanTest extends CodeExecutionTestHelper {

	public function testIsSubtypeOf(): void {
		$result = $this->executeCodeSnippet("`Real > `Integer;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfRange1(): void {
		$result = $this->executeCodeSnippet("`Integer<2..10> > `Integer<1..11>;");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfRange2(): void {
		$result = $this->executeCodeSnippet("`Integer<2..10> > `Integer<2..10>;");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfRange3(): void {
		$result = $this->executeCodeSnippet("`Integer<2..10> > `Integer<3..9>;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfMetaTypeNamed(): void {
		$result = $this->executeCodeSnippet("`Atom > `Named;");
		$this->assertEquals("false", $result);
	}

	public function testIsSubtypeOfMetaTypeNamedFalse(): void {
		$result = $this->executeCodeSnippet("`Named > `Atom;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfMetaTypeEnumeration(): void {
		$result = $this->executeCodeSnippet("`Enumeration > `EnumerationSubset;");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "`Integer > 3.14;");
	}

}