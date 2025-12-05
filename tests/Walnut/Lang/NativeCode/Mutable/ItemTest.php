<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testItemMutableArrayValid(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, [1, 2, 3]}->item(1);");
		$this->assertEquals("2", $result);
	}

	public function testItemMutableArrayOutOfBounds(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, [1, 2, 3]}->item(5);");
		$this->assertEquals("@IndexOutOfRange![index: 5]", $result);
	}

	public function testItemMutableTupleValid(): void {
		$result = $this->executeCodeSnippet("mutable{[Integer, String, Boolean], [42, 'hello', true]}->item(1);");
		$this->assertEquals("'hello'", $result);
	}

	public function testItemMutableMapValid(): void {
		$result = $this->executeCodeSnippet("mutable{Map<String:Integer>, [a: 1, b: 2, c: 3]}->item('b');");
		$this->assertEquals("2", $result);
	}

	public function testItemMutableMapMissingKey(): void {
		$result = $this->executeCodeSnippet("mutable{Map<String:Integer>, [a: 1, b: 2]}->item('z');");
		$this->assertEquals("@MapItemNotFound![key: 'z']", $result);
	}

	public function testItemMutableRecordValid(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Integer, b: String], [a: 10, b: 'test']}->item('a');");
		$this->assertEquals("10", $result);
	}

	public function testItemMutableRecordOptionalKeyPresent(): void {
		$result = $this->executeCodeSnippet(
			"getValue(mutable{[a: String, b: ?Real], [a: 'hello', b: 2]});",
			valueDeclarations: "getValue = ^m: Mutable<[a: String, b: ?Real]> => Result<Real, MapItemNotFound> :: m->item('b');"
		);
		$this->assertEquals("2", $result);
	}

	public function testItemMutableRecordOptionalKeyMissing(): void {
		$result = $this->executeCodeSnippet(
			"getValue(mutable{[a: String, b: ?Real], [a: 'hello']});",
			valueDeclarations: "getValue = ^m: Mutable<[a: String, b: ?Real]> => Result<Real, MapItemNotFound> :: m->item('b');"
		);
		$this->assertEquals("@MapItemNotFound![key: 'b']", $result);
	}

	public function testItemInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Set, [;]}->item(0);");
	}

	public function testItemInvalidParameterTypeForArray(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"mutable{Array<Integer>, [1, 2, 3]}->item('a')");
	}

	public function testItemInvalidParameterTypeForMap(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"mutable{Map<String:Integer>, [a: 1, b: 2]}->item(0)");
	}

}
