<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

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
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Set<Integer>, [1; 2; 3]}->ADD('hi');");
	}

	public function testAddInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->ADD(2);");
	}

	public function testAddInvalidTargetSetRange(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Set<..10>, [1; 2; 3]}->ADD(2);");
	}

	public function testAddInvalidTargetMapRange(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Map<..10>, [a: 1, b: 2, c: 3]}->ADD(2);");
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
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Map<String:Integer>, [a: 1, b: 2, c: 3]}->ADD('hi');");
	}

	public function testMapAddInvalidParameterValueType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Map<String:Integer>, [a: 1, b: 2, c: 3]}->ADD[key: 'd', 'value': 'str'];");
	}

	public function testMapAddInvalidParameterKeyType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Map<String<1>:Integer>, [a: 1, b: 2, c: 3]}->ADD[key: 'dd', 'value': 4];");
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
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD('hi');");
	}

	public function testRecordAddInvalidParameterValueTypeRest(): void {
		$this->executeErrorCodeSnippet('the item with key d cannot be of type Integer[1], String expected', "mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'd', 'value': 1];");
	}

	public function testRecordAddInvalidParameterValueTypeOptionalField(): void {
		$this->executeErrorCodeSnippet("the item with key b cannot be of type String['hello'], Integer expected", "mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'b', 'value': 'hello'];");
	}

	public function testRecordAddInvalidParameterValueTypeField(): void {
		$this->executeErrorCodeSnippet('the item with key a cannot be of type True, Real expected', "mutable{[a: Real, b: ?Integer, ...String], [a: 1, b: 2, c: 'hello']}->ADD[key: 'a', 'value': true];");
	}

	public function testRecordAddInvalidParameterKeyType(): void {
		$this->executeErrorCodeSnippet('an item with key dd cannot be added', "mutable{[a: Real, b: ?Integer, c: Integer], [a: 1, b: 2, c: 3]}->ADD[key: 'dd', 'value': 4];");
	}

	public function testRecordAddUseMapType(): void {
		$result = $this->executeCodeSnippet("r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?Integer, ...Integer]> :: mutable{[a: Real, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->ADD[key: s, value: 42];"
		);
		$this->assertEquals("mutable{[\n	a: Real,\n	b: OptionalKey<Integer>,\n... Integer\n], [a: 1, b: 42, c: 3]}", $result);
	}

	public function testRecordAddMapTypeInvalidValueType(): void {
		$this->executeErrorCodeSnippet("the value type String['hello'] should be a subtype of Integer",
			"r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?String, ...Integer]> :: mutable{[a: Real, b: ?String, ...Integer], [a: 1, b: 'hello', c: 3]}->ADD[key: s, value: 'hello'];"
		);
	}

	public function testRecordAddMapTypeInvalidRestRelation(): void {
		$this->executeErrorCodeSnippet('the value type Integer[20] of item b should be a subtype of Integer',
			"r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?String, ...Integer]> :: mutable{[a: Real, b: ?String, ...Integer], [a: 1, b: 'hello', c: 3]}->ADD[key: s, value: 20];"
		);
	}

	public function testRecordAddMapTypeRestTypeNothing(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"r('b');",
			valueDeclarations: "r = ^s: String => Mutable<[a: Real, b: ?String, ...Integer]> :: mutable{[a: Real, b: ?Integer], [a: 1, b: 42]}->ADD[key: s, value: 13];"
		);
	}

}