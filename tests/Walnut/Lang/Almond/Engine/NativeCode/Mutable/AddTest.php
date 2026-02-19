<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AddTest extends CodeExecutionTestHelper {

	public function testSetAddNew(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->ADD(5);");
		$this->assertEquals("mutable{Set, [1; 2; 3; 5]}", $result);
	}

	public function testSetAddExisting(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->ADD(2);");
		$this->assertEquals("mutable{Set, [1; 2; 3]}", $result);
	}

	public function testSetAddInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['hi'] is not a subtype of the set item type Integer",
			"mutable{Set<Integer>, [1; 2; 3]}->ADD('hi');");
	}

	public function testAddInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set, Record or Map type with an unbounded number of items, got Real', "mutable{Real, 3.14}->ADD(2);");
	}

	public function testAddInvalidTargetSetRange(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set, Record or Map type with an unbounded number of items, got Set<..10>', "mutable{Set<..10>, [1; 2; 3]}->ADD(2);");
	}

	public function testAddInvalidTargetMapRange(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be a Set, Record or Map type with an unbounded number of items, got Map<..10>', "mutable{Map<..10>, [a: 1, b: 2, c: 3]}->ADD(2);");
	}

	public function testMapAddNew(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [a: 1, b: 2, c: 3]}->ADD[key: 'e', value: 5];");
		$this->assertEquals("mutable{Map, [a: 1, b: 2, c: 3, e: 5]}", $result);
	}

	public function testMapAddExisting(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [a: 1, b: 2, c: 3]}->ADD[key: 'c', value: 5];");
		$this->assertEquals("mutable{Map, [a: 1, b: 2, c: 5]}", $result);
	}

	public function testMapAddInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['hi'] is not a valid key-value record for the map type Map<Integer>",
			"mutable{Map<String:Integer>, [a: 1, b: 2, c: 3]}->ADD('hi');");
	}

	public function testMapAddInvalidParameterValueType(): void {
		$this->executeErrorCodeSnippet("The parameter type [key: String['d'], value: String['str']] is not a valid key-value record for the map type Map<Integer>",
			"mutable{Map<String:Integer>, [a: 1, b: 2, c: 3]}->ADD[key: 'd', 'value': 'str'];");
	}

	public function testMapAddInvalidParameterKeyType(): void {
		$this->executeErrorCodeSnippet("The parameter type [key: String['dd'], value: Integer[4]] is not a valid key-value record for the map type Map<String<1>:Integer>",
			"mutable{Map<String<1>:Integer>, [a: 1, b: 2, c: 3]}->ADD[key: 'dd', 'value': 4];");
	}

	public function testRecordAddOptionalNew(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Real, b: ?Integer, ...String], [a: 1, c: 'hello']}->ADD[key: 'b', value: 22];");
		$this->assertEquals("mutable{[\n	a: Real,\n	b: OptionalKey<Integer>,\n... String\n], [a: 1, c: 'hello', b: 22]}", $result);
	}

	public function testRecordAddExistingField(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'a', value: 3.14];");
		$this->assertEquals("mutable{[\n	a: Real,\n	b: OptionalKey<Integer>,\n... String\n], [a: 3.14, b: 2, c: 'hello']}", $result);
	}

	public function testRecordAddExistingOptional(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'b', value: 22];");
		$this->assertEquals("mutable{[\n	a: Real,\n	b: OptionalKey<Integer>,\n... String\n], [a: 1, b: 22, c: 'hello']}", $result);
	}

	public function testRecordAddExistingRest(): void {
		$result = $this->executeCodeSnippet("mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'c', value: 'world'];");
		$this->assertEquals("mutable{[\n	a: Real,\n	b: OptionalKey<Integer>,\n... String\n], [a: 1, b: 2, c: 'world']}", $result);
	}

	public function testRecordAddInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['hi'] is not a valid key-value record for the record type",
			"mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD('hi');");
	}

	public function testRecordAddInvalidParameterValueTypeRest(): void {
		$this->executeErrorCodeSnippet("The value type Integer[1] for key 'd' is not a subtype of String",
			"mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'd', 'value': 1];");
	}

	public function testRecordAddInvalidParameterValueTypeOptionalField(): void {
		$this->executeErrorCodeSnippet("The value type String['hello'] for key 'b' is not a subtype of Integer",
			"mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'b', 'value': 'hello'];");
	}

	public function testRecordAddInvalidParameterValueTypeField(): void {
		$this->executeErrorCodeSnippet("The value type True for key 'a' is not a subtype of Real",
			"mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'a', 'value': true];");
	}

	public function testRecordAddInvalidParameterKeyType(): void {
		$this->executeErrorCodeSnippet("An item with key 'dd' cannot be added to this record type",
			"mutable{[a: Real, b: ?Integer, c: Integer], [a: 1, b: 2, c: 3]}->ADD[key: 'dd', 'value': 4];");
	}

	public function testRecordAddUseMapType(): void {
		$result = $this->executeCodeSnippet("r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?Integer, ...Integer]> :: mutable{[a: Real, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->ADD[key: s, value: 42];"
		);
		$this->assertEquals("mutable{[\n	a: Real,\n	b: OptionalKey<Integer>,\n... Integer\n], [a: 1, b: 42, c: 3]}", $result);
	}

	public function testRecordAddMapTypeInvalidValueType(): void {
		$this->executeErrorCodeSnippet("The value type String['hello'] should be a subtype of the rest type Integer",
			"r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?String, ...Integer]> :: mutable{[a: Real, b: ?String, ...Integer], [a: 1, b: 'hello', c: 3]}->ADD[key: s, value: 'hello'];"
		);
	}

	public function testRecordAddMapTypeInvalidRestRelation(): void {
		$this->executeErrorCodeSnippet("The rest type Integer is not a subtype of the type OptionalKey<String> for key 'b'",
			"r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?String, ...Integer]> :: mutable{[a: Real, b: ?String, ...Integer], [a: 1, b: 'hello', c: 3]}->ADD[key: s, value: 20];"
		);
	}

	public function testRecordAddMapTypeRestTypeNothing(): void {
		$this->executeErrorCodeSnippet('The value type Integer[13] should be a subtype of the rest type Nothing',
			"r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?String, ...Integer]> :: mutable{[a: Real, b: ?Integer], [a: 1, b: 42]}->ADD[key: s, value: 13];"
		);
	}

}