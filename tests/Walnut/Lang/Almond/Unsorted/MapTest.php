<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapStringKey(): void {
		$result = $this->executeCodeSnippet("`Map<String<1..3>|String['hello']:Real>;");
		$this->assertEquals("type{Map<(String<1..3>|String['hello']):Real>}", $result);
	}

	public function testMapStringKeyAlias(): void {
		$result = $this->executeCodeSnippet(
			"`Map<MyString:Real>;",
			"MyString = String<1..3>|String['hello'];",
		);
		$this->assertEquals("type{Map<MyString:Real>}", $result);
	}

	public function testStrictSupersetCastError(): void {
		// The cast may return an error and therefore an implicit usage is not allowed.
		$this->executeErrorCodeSnippet(
			"Map key type must be a subtype of String, Integer given.",
			"`Map<Integer:String>"
		);
	}

}