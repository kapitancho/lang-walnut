<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class CombineAsStringTest extends CodeExecutionTestHelper {

	public function testCombineAsStringEmpty(): void {
		$result = $this->executeCodeSnippet("[]->combineAsString('---');");
		$this->assertEquals("''", $result);
	}

	public function testCombineAsStringNonEmptyNoSeparator(): void {
		$result = $this->executeCodeSnippet("['1', '2', '5', '10', '5']->combineAsString('');");
		$this->assertEquals("'125105'", $result);
	}

	public function testCombineAsStringNonEmptyWithSeparator(): void {
		$result = $this->executeCodeSnippet("['1', '2', '5', '10', '5']->combineAsString(' ');");
		$this->assertEquals("'1 2 5 10 5'", $result);
	}

	public function testCombineAsStringInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The item type of the target array must be a subtype of String, got Integer[1, 2]', "[1, 2]->combineAsString(' ');");
	}

	public function testCombineAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['1', '2']->combineAsString(0);");
	}

	// Refined type tests - covering the new branches in getValidator()

	public function testCombineAsStringTypeRefinedEmptyArrayType(): void {
		// Array with max length 0 → always returns empty string subset
		$result = $this->executeCodeSnippet(
			"g[];",
			valueDeclarations: "g = ^p: Array<String, 0> => String[''] :: p->combineAsString('---');"
		);
		$this->assertEquals("''", $result);
	}

	public function testCombineAsStringTypeRefinedPreciseBounds(): void {
		// Array<String<2..3>, 2..3> + separator ' ' (len 1..1)
		// min = 2*2 + (2-1)*1 = 5, max = 3*3 + (3-1)*1 = 11 → String<5..11>
		$result = $this->executeCodeSnippet(
			"g['ab', 'cde'];",
			valueDeclarations: "g = ^p: Array<String<2..3>, 2..3> => String<5..11> :: p->combineAsString(' ');"
		);
		$this->assertEquals("'ab cde'", $result);
	}

	public function testCombineAsStringTypeRefinedSingleElement(): void {
		// Single element: separator never applied
		// Array<String<3..5>, 1> + separator '-' → String<3..5>
		$result = $this->executeCodeSnippet(
			"g['hello'];",
			valueDeclarations: "g = ^p: Array<String<3..5>, 1> => String<3..5> :: p->combineAsString('-');"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testCombineAsStringTypeRefinedZeroMinArrayLength(): void {
		// Array min length 0 → result min length is 0
		// Array<String<2..3>, 0..3> + separator ' ' → String<0..11>
		$result = $this->executeCodeSnippet(
			"g['ab', 'cde'];",
			valueDeclarations: "g = ^p: Array<String<2..3>, ..3> => String<..11> :: p->combineAsString(' ');"
		);
		$this->assertEquals("'ab cde'", $result);
	}

	public function testCombineAsStringTypeRefinedPlusInfinityItemMax(): void {
		// Item max is PlusInfinity → result max is PlusInfinity
		// Array<String<1..>, 2..3> + separator ' ' → String<3..>
		$result = $this->executeCodeSnippet(
			"g['hello', 'world'];",
			valueDeclarations: "g = ^p: Array<String<1..>, 2..3> => String<3..> :: p->combineAsString(' ');"
		);
		$this->assertEquals("'hello world'", $result);
	}

	public function testCombineAsStringTypeRefinedPlusInfinityArrayMax(): void {
		// Array max is PlusInfinity → result max is PlusInfinity
		// Array<String<1..2>, 1..> + separator '-' → String<1..>
		$result = $this->executeCodeSnippet(
			"g['a', 'bb'];",
			valueDeclarations: "g = ^p: Array<String<1..2>, 1..> => String<1..> :: p->combineAsString('-');"
		);
		$this->assertEquals("'a-bb'", $result);
	}

	public function testCombineAsStringTypeRefinedPlusInfinitySeparatorMax(): void {
		// Separator max is PlusInfinity → result max is PlusInfinity
		// Array<String<1..2>, 2..3> + separator String<1..> → String<3..>
		$result = $this->executeCodeSnippet(
			"g[arr: ['a', 'bb'], sep: '---'];",
			valueDeclarations: "g = ^[arr: Array<String<1..2>, 2..3>, sep: String<1..>] => String<3..> :: #arr->combineAsString(#sep);"
		);
		$this->assertEquals("'a---bb'", $result);
	}

}