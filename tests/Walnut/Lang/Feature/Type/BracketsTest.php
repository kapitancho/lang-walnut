<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BracketsTest extends CodeExecutionTestHelper {

	public function testBasicType(): void {
		$result = $this->executeCodeSnippet("type{Type1};", <<<NUT
		Type1 = (Integer|String)&Boolean;
	NUT);
		$this->assertEquals("type{Type1}", $result);
	}

	public function testBasicTypeUnion(): void {
		$result = $this->executeCodeSnippet("type{Type1|Type2};", <<<NUT
		Type1 = Integer;
		Type2 = String;
	NUT);
		$this->assertEquals("type{(Type1|Type2)}", $result);
	}

	public function testTypesInBracketsArray(): void {
		$result = $this->executeCodeSnippet("type[Type1, Type2, Type3, Type4];", <<<NUT
		Type1 = (Integer|String)&Boolean;
		Type2 = (Integer&String)|Boolean;
		Type3 = Integer|(String&Boolean);
		Type4 = Integer&(String|Boolean);
	NUT);
		$this->assertEquals("type[Type1, Type2, Type3, Type4]", $result);
	}

	public function testTypesInBracketsMap(): void {
		$result = $this->executeCodeSnippet("type[t1: Type1, t2: Type2];", <<<NUT
		Type1 = (Integer|String)&Boolean;
		Type2 = (Integer&String)|Boolean;
		Type3 = Integer|(String&Boolean);
		Type4 = Integer&(String|Boolean);
	NUT);
		$this->assertEquals("type[t1: Type1, t2: Type2]", $result);
	}

	public function testShortSyntaxBasicType(): void {
		$result = $this->executeCodeSnippet("`Type1;", <<<NUT
		Type1 = (Integer|String)&Boolean;
	NUT);
		$this->assertEquals("type{Type1}", $result);
	}

	public function testShortSyntaxBasicTypeUnion(): void {
		$result = $this->executeCodeSnippet("`Type1|Type2;", <<<NUT
		Type1 = Integer;
		Type2 = String;
	NUT);
		$this->assertEquals("type{(Type1|Type2)}", $result);
	}

	public function testShortSyntaxTypesInBracketsArray(): void {
		$result = $this->executeCodeSnippet("`[Type1, Type2, Type3, Type4];", <<<NUT
		Type1 = (Integer|String)&Boolean;
		Type2 = (Integer&String)|Boolean;
		Type3 = Integer|(String&Boolean);
		Type4 = Integer&(String|Boolean);
	NUT);
		$this->assertEquals("type[Type1, Type2, Type3, Type4]", $result);
	}

	public function testShortSyntaxTypesInBracketsMap(): void {
		$result = $this->executeCodeSnippet("`[t1: Type1, t2: Type2];", <<<NUT
		Type1 = (Integer|String)&Boolean;
		Type2 = (Integer&String)|Boolean;
		Type3 = Integer|(String&Boolean);
		Type4 = Integer&(String|Boolean);
	NUT);
		$this->assertEquals("type[t1: Type1, t2: Type2]", $result);
	}


}