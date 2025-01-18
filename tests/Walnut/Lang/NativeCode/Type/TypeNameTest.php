<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TypeNameTest extends CodeExecutionTestHelper {

	public function testTypeNameNameMetaType(): void {
		$result = $this->executeCodeSnippet("getTypeName(type{MyAtom});", "MyAtom = :[]; getTypeName = ^Type<Named> => String<1..> :: #->typeName;");
		$this->assertEquals("'MyAtom'", $result);
	}

	public function testTypeNameAtom(): void {
		$result = $this->executeCodeSnippet("type{MyAtom}->typeName;", "MyAtom = :[];");
		$this->assertEquals("'MyAtom'", $result);
	}

	public function testTypeNameAtomMetaType(): void {
		$result = $this->executeCodeSnippet("getTypeName(type{MyAtom});", "MyAtom = :[]; getTypeName = ^Type<Atom> => String<1..> :: #->typeName;");
		$this->assertEquals("'MyAtom'", $result);
	}

	public function testTypeNameEnumeration(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration}->typeName;", "MyEnumeration = :[A, B];");
		$this->assertEquals("'MyEnumeration'", $result);
	}

	public function testTypeNameEnumerationMetaType(): void {
		$result = $this->executeCodeSnippet("getTypeName(type{MyEnumeration});", "MyEnumeration = :[A, B]; getTypeName = ^Type<Enumeration> => String<1..> :: #->typeName;");
		$this->assertEquals("'MyEnumeration'", $result);
	}

	public function testTypeNameAlias(): void {
		$result = $this->executeCodeSnippet("type{MyAlias}->typeName;", "MyAlias = String;");
		$this->assertEquals("'MyAlias'", $result);
	}

	public function testTypeNameAliasMetaType(): void {
		$result = $this->executeCodeSnippet("getTypeName(type{MyAlias});", "MyAlias = String; getTypeName = ^Type<Alias> => String<1..> :: #->typeName;");
		$this->assertEquals("'MyAlias'", $result);
	}

	public function testTypeNameSubtype(): void {
		$result = $this->executeCodeSnippet("type{MySubtype}->typeName;", "MySubtype <: String;");
		$this->assertEquals("'MySubtype'", $result);
	}

	public function testTypeNameSubtypeMetaType(): void {
		$result = $this->executeCodeSnippet("getTypeName(type{MySubtype});", "MySubtype <: String; getTypeName = ^Type<Subtype> => String<1..> :: #->typeName;");
		$this->assertEquals("'MySubtype'", $result);
	}

	public function testTypeNameSealed(): void {
		$result = $this->executeCodeSnippet("type{MySealed}->typeName;", "MySealed = $[a: String];");
		$this->assertEquals("'MySealed'", $result);
	}

	public function testTypeNameSealedMetaType(): void {
		$result = $this->executeCodeSnippet("getTypeName(type{MySealed});", "MySealed = $[a: String]; getTypeName = ^Type<Sealed> => String<1..> :: #->typeName;");
		$this->assertEquals("'MySealed'", $result);
	}

}