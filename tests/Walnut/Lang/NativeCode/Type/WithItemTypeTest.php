<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithItemTypeTest extends CodeExecutionTestHelper {

	public function testWithItemTypeArray(): void {
		$result = $this->executeCodeSnippet("type{Array<String>}->withItemType(type{Integer});");
		$this->assertEquals("type{Array<Integer>}", $result);
	}

	public function testWithItemTypeMap(): void {
		$result = $this->executeCodeSnippet("type{Map<String>}->withItemType(type{Integer});");
		$this->assertEquals("type{Map<Integer>}", $result);
	}

	public function testWithItemTypeSet(): void {
		$result = $this->executeCodeSnippet("type{Set<String>}->withItemType(type{Integer});");
		$this->assertEquals("type{Set<Integer>}", $result);
	}

	public function testWithItemTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withItemType(type{Integer});");
	}

	public function testWithItemTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Set<String>}->withItemType(1)");
	}

}