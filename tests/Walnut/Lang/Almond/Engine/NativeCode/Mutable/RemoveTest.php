<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class RemoveTest extends CodeExecutionTestHelper {

	public function testSetRemoveNew(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->REMOVE(5);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testSetRemoveExisting(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->REMOVE(2);");
		$this->assertEquals("2", $result);
	}

	public function testSetRemoveInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set type with minimum number of elements 0, a Record type with at least one optional key or an open Record type, or a Map type with minimum number of elements 0, got Set<1..>', "mutable{Set<1..>, [1; 2; 3]}->REMOVE(2);");
	}

	public function testRemoveInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set type with minimum number of elements 0, a Record type with at least one optional key or an open Record type, or a Map type with minimum number of elements 0, got Real', "mutable{Real, 3.14}->REMOVE(2);");
	}

	public function testMapRemoveNew(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [a: 1, b: 2, c: 3]}->REMOVE('d');");
		$this->assertEquals("@MapItemNotFound![key: 'd']", $result);
	}

	public function testMapRemoveExisting(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [a: 1, b: 2, c: 3]}->REMOVE('b');");
		$this->assertEquals("2", $result);
	}

	public function testMapRemoveInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set type with minimum number of elements 0, a Record type with at least one optional key or an open Record type, or a Map type with minimum number of elements 0, got Map<1..>', "mutable{Map<1..>, [a: 1, b: 2, c: 3]}->REMOVE('a');");
	}

	public function testMapRemoveInvalidTargetTypeKey(): void {
		$this->executeErrorCodeSnippet("The parameter type String['abc'] is not a subtype of the map key type String<1>",
			"mutable{Map<String<1>: Any>, [a: 1, b: 2, c: 3]}->REMOVE('abc');");
	}

	public function testRecordRemoveRestMissing(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Integer, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->REMOVE('d');");
		$this->assertEquals("@MapItemNotFound![key: 'd']", $result);
	}

	public function testRecordRemoveRestPresent(): void {
		$result = $this->executeCodeSnippet("v = mutable{[a: Integer, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}; [v->REMOVE('c'), v];");
		$this->assertEquals("[\n	3,\n	mutable{[\n		a: Integer,\n		b: OptionalKey<Integer>,\n	... Integer\n	], [a: 1, b: 2]}\n]", $result);
	}

	public function testRecordRemoveOptionalMissing(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Integer, d: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->REMOVE('d');");
		$this->assertEquals("@MapItemNotFound![key: 'd']", $result);
	}

	public function testRecordRemoveOptionalPresent(): void {
		$result = $this->executeCodeSnippet("v = mutable{[a: Integer, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}; [v->REMOVE('b'), v];");
		$this->assertEquals("[\n	2,\n	mutable{[\n		a: Integer,\n		b: OptionalKey<Integer>,\n	... Integer\n	], [a: 1, c: 3]}\n]", $result);
	}

	// The record type has neither optional fields nor a rest type
	public function testRecordRemoveInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set type with minimum number of elements 0, a Record type with at least one optional key or an open Record type, or a Map type with minimum number of elements 0, got [a: Integer, b: Integer, c: Integer]', "mutable{[a: Integer, b: Integer, c: Integer], [a: 1, b: 2, c: 3]}->REMOVE('a');");
	}

	// The key 'a' is of type Integer and is not optional or part of the rest
	public function testRecordRemoveInvalidParameterTypeKey(): void {
		$this->executeErrorCodeSnippet("Cannot remove required record key 'a' of type Integer", "mutable{[a: Integer, b: ?Integer, c: Integer], [a: 1, b: 2, c: 3]}->REMOVE('a');");
	}

	// The key 'a' is of type Integer and is not optional or part of the rest
	public function testRecordRemoveInvalidParameterTypeKeyRest(): void {
		$this->executeErrorCodeSnippet("Cannot remove unknown record key 'd' from a closed record type", "mutable{[a: Integer, b: ?Integer, c: Integer], [a: 1, b: 2, c: 3]}->REMOVE('d');");
	}

	public function testRecordRemoveUseMapType(): void {
		$result = $this->executeCodeSnippet("r('b');",
			valueDeclarations: "r = ^s: String => Result<Real, MapItemNotFound> :: v = mutable{[a: Real, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->REMOVE(s);"
		);
		$this->assertEquals("2", $result);
	}

	public function testRecordRemoveUseMapTypeNotFound(): void {
		$result = $this->executeCodeSnippet("r('x');",
			valueDeclarations: "r = ^s: String => Result<Real, MapItemNotFound> :: v = mutable{[a: Real, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->REMOVE(s);"
		);
		$this->assertEquals("@MapItemNotFound![key: 'x']", $result);
	}

}