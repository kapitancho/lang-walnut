<?php

namespace Walnut\Lang\NativeCode\JsonValue;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class HydrateAsTest extends CodeExecutionTestHelper {

	public function testHydrateAsAny(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Any});");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsNothing(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Nothing});");
		$this->assertEquals("@HydrationError![\n\tvalue: 42,\n\thydrationPath: 'value',\n\terrorMessage: 'There is no value allowed, 42 provided'\n]", $result);
	}

	public function testHydrateAsFunction(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{^Any => Any});");
		$this->assertEquals("@HydrationError![\n\tvalue: 42,\n\thydrationPath: 'value',\n\terrorMessage: 'Functions cannot be hydrated'\n]", $result);
	}

	public function testHydrateAsIntersection(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{[a: String, ... Real]&[b: Real, ... String]});");
		$this->assertEquals("@HydrationError![\n\tvalue: 42,\n\thydrationPath: 'value',\n\terrorMessage: 'Intersection type values cannot be hydrated'\n]", $result);
	}


	public function testHydrateAsNullFromNull(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Null});");
		$this->assertEquals("null", $result);
	}

	public function testHydrateAsNullFromOther(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(type{Null});");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be \`null\`'\n]", $result);
	}

	public function testHydrateAsAtomFromNull(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(`MyAtom);", "MyAtom := ();");
		$this->assertEquals("MyAtom", $result);
	}

	public function testHydrateAsAtomFromOther(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(`MyAtom);", "MyAtom := ();");
		$this->assertEquals("MyAtom", $result);
	}

	public function testHydrateAsAtomFromNullCastOk(): void {
		$result = $this->executeCodeSnippet("true->hydrateAs(`MyAtom);",
			"MyAtom := (); JsonValue ==> MyAtom @ String :: ?whenValueOf($) is { true: MyAtom, ~ : @'error' };");
		$this->assertEquals("MyAtom", $result);
	}

	public function testHydrateAsAtomFromNullCastError(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(`MyAtom);",
			"MyAtom := (); JsonValue ==> MyAtom @ String :: ?whenValueOf($) is { true: MyAtom, ~ : @'error' };");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'Atom hydration failed. Error: \`error\`'\n]", $result);
	}

	public function testHydrateAsTrueFromTrue(): void {
		$result = $this->executeCodeSnippet("true->hydrateAs(`True);");
		$this->assertEquals("true", $result);
	}

	public function testHydrateAsTrueFromFalse(): void {
		$result = $this->executeCodeSnippet("false->hydrateAs(`True);");
		$this->assertEquals("@HydrationError![\n\tvalue: false,\n\thydrationPath: 'value',\n\terrorMessage: 'The boolean value should be true'\n]", $result);
	}

	public function testHydrateAsTrueFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(`True);");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be \`true\`'\n]", $result);
	}

	public function testHydrateAsFalseFromFalse(): void {
		$result = $this->executeCodeSnippet("false->hydrateAs(`False);");
		$this->assertEquals("false", $result);
	}

	public function testHydrateAsFalseFromTrue(): void {
		$result = $this->executeCodeSnippet("true->hydrateAs(`False);");
		$this->assertEquals("@HydrationError![\n\tvalue: true,\n\thydrationPath: 'value',\n\terrorMessage: 'The boolean value should be false'\n]", $result);
	}

	public function testHydrateAsFalseFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(`False);");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be \`false\`'\n]", $result);
	}

	public function testHydrateAsBooleanFromFalse(): void {
		$result = $this->executeCodeSnippet("false->hydrateAs(`Boolean);");
		$this->assertEquals("false", $result);
	}

	public function testHydrateAsBooleanFromTrue(): void {
		$result = $this->executeCodeSnippet("true->hydrateAs(`Boolean);");
		$this->assertEquals("true", $result);
	}

	public function testHydrateAsBooleanFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(`Boolean);");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a boolean'\n]", $result);
	}

	public function testHydrateAsStringFromString(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(type{String});");
		$this->assertEquals("'hello'", $result);
	}


	public function testHydrateAsStringFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{String});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a length between 0 and +Infinity'\n]", $result);
	}

	public function testHydrateAsStringRangeFromStringInRange(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(type{String<2..10>});");
		$this->assertEquals("'hello'", $result);
	}

	public function testHydrateAsStringRangeFromStringOutOfRange(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(type{String<2..4>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value',\n\terrorMessage: 'The string value should be with a length between 2 and 4'\n]", $result);
	}

	public function testHydrateAsStringSubsetFromStringInSubset(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(type{String['hello', 'world']});");
		$this->assertEquals("'hello'", $result);
	}

	public function testHydrateAsStringSubsetFromStringOutOfSubset(): void {
		$result = $this->executeCodeSnippet("'hello'->hydrateAs(type{String['welcome', 'world']});");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value',\n\terrorMessage: 'The string value should be among welcome, world'\n]", $result);
	}

	public function testHydrateAsStringSubsetFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{String['hello', 'world']});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string among hello, world'\n]", $result);
	}


	public function testHydrateAsIntegerFromInteger(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Integer});");
		$this->assertEquals("42", $result);
	}


	public function testHydrateAsIntegerFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Integer});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be an integer in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsIntegerRangeFromIntegerInRange(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Integer<22..50>});");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsIntegerRangeFromIntegerOutOfRange(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Integer<12..34>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 42,\n\thydrationPath: 'value',\n\terrorMessage: 'The integer value should be in [12..34]'\n]", $result);
	}

	public function testHydrateAsIntegerSubsetFromIntegerInSubset(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Integer[12, 22, 32, 42]});");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsIntegerSubsetFromStringOutOfSubset(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Integer[15, 25, 35, 45]});");
		$this->assertEquals("@HydrationError![\n\tvalue: 42,\n\thydrationPath: 'value',\n\terrorMessage: 'The integer value should be in 15, 25, 35, 45'\n]", $result);
	}

	public function testHydrateAsIntegerSubsetFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Integer[12, 22, 32, 42]});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be an integer in 12, 22, 32, 42'\n]", $result);
	}




	public function testHydrateAsRealFromReal(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Real});");
		$this->assertEquals("3.14", $result);
	}

	public function testHydrateAsRealFromInteger(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Real});");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsRealFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Real});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a real number in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsRealRangeFromRealInRange(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Real<2.2..5>});");
		$this->assertEquals("3.14", $result);
	}

	public function testHydrateAsRealRangeFromRealOutOfRange(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Real<12..34.7>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The real value should be in [12..34.7]'\n]", $result);
	}

	public function testHydrateAsRealSubsetFromRealInSubset(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Real[1.2, 2, 3.14]});");
		$this->assertEquals("3.14", $result);
	}

	public function testHydrateAsRealSubsetFromIntegerInSubset(): void {
		$result = $this->executeCodeSnippet("2->hydrateAs(type{Real[1.2, 2, 3.14]});");
		$this->assertEquals("2", $result);
	}

	public function testHydrateAsRealSubsetFromStringOutOfSubset(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Real[1.5, 2, 3.5]});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The real value should be in 1.5, 2, 3.5'\n]", $result);
	}

	public function testHydrateAsRealSubsetFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Real[1.5, 2, 3.5]});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a real number in 1.5, 2, 3.5'\n]", $result);
	}



	public function testHydrateAsArrayFromArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Array});");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsArrayFromArrayInLengthRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Array<1..5>});");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsArrayFromArrayOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Array<11..15>});");
		$this->assertEquals("@HydrationError![\n\tvalue: [1, 2, 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'The array value should be with a length between 11 and 15'\n]", $result);
	}

	public function testHydrateAsArrayFromArrayItemTypeCorrect(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Array<Integer|String>});");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsArrayFromArrayItemTypeIncorrect(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Array<Integer>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value[2]',\n\terrorMessage: 'The value should be an integer in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsArrayFromArrayItemTypeAndLengthRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Array<Integer|String, ..4>});");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsArrayFromSetItemTypeAndLengthRange(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Array<Integer|String, ..4>});");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsArrayFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Array});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be an array with a length between 0 and +Infinity'\n]", $result);
	}



	public function testHydrateAsMapFromMap(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{Map});");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsMapFromMapInLengthRange(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{Map<1..5>});");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsMapFromMapOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{Map<11..15>});");
		$this->assertEquals("@HydrationError![\n\tvalue: [a: 1, b: 2, c: 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'The map value should be with a length between 11 and 15'\n]", $result);
	}

	public function testHydrateAsMapFromMapItemTypeCorrect(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{Map<Integer|String>});");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsMapFromMapItemTypeIncorrect(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{Map<Integer>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value.c',\n\terrorMessage: 'The value should be an integer in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsMapFromMapItemTypeAndLengthRange(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{Map<Integer|String, ..4>});");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsMapFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Map});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a map with a length between 0 and +Infinity'\n]", $result);
	}



	public function testHydrateAsSetFromSet(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Set});");
		$this->assertEquals("[1; 2; 'hello']", $result);
	}

	public function testHydrateAsSetFromSetInLengthRange(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Set<1..5>});");
		$this->assertEquals("[1; 2; 'hello']", $result);
	}

	public function testHydrateAsSetFromSetOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Set<11..15>});");
		$this->assertEquals("@HydrationError![\n\tvalue: [1; 2; 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'The set value should be with a length between 11 and 15'\n]", $result);
	}

	public function testHydrateAsSetFromSetOutOfLengthRangeDueToUniqueness(): void {
		$result = $this->executeCodeSnippet("[1, 1, 1, 1, 2]->hydrateAs(type{Set<4..>});");
		$this->assertEquals("@HydrationError![\n\tvalue: [1, 1, 1, 1, 2],\n\thydrationPath: 'value',\n\terrorMessage: 'The set value should be with a length between 4 and +Infinity'\n]", $result);
	}

	public function testHydrateAsSetFromSetItemTypeCorrect(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Set<Integer|String>});");
		$this->assertEquals("[1; 2; 'hello']", $result);
	}

	public function testHydrateAsSetFromSetItemTypeIncorrect(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Set<Integer>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value[2]',\n\terrorMessage: 'The value should be an integer in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsSetFromSetItemTypeAndLengthRange(): void {
		$result = $this->executeCodeSnippet("[1; 2; 'hello']->hydrateAs(type{Set<Integer|String, ..4>});");
		$this->assertEquals("[1; 2; 'hello']", $result);
	}

	public function testHydrateAsSetFromArrayItemTypeAndLengthRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type{Set<Integer|String, ..4>});");
		$this->assertEquals("[1; 2; 'hello']", $result);
	}

	public function testHydrateAsSetFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{Set});");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be an set with a length between 0 and +Infinity'\n]", $result);
	}




	public function testHydrateAsRecordFromRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type[a: Integer, b: Integer, c: String]);");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsRecordFromRecordWrongPropertyType(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type[a: Integer, b: Integer, c: Real]);");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value.c',\n\terrorMessage: 'The value should be a real number in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsRecordFromRecordMissingProperty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->hydrateAs(type[a: Integer, b: Integer, c: Real]);");
		$this->assertEquals("@HydrationError![\n\tvalue: [a: 1, b: 2],\n\thydrationPath: 'value',\n\terrorMessage: 'The record value should contain the key c'\n]", $result);
	}

	public function testHydrateAsRecordWithRestTypeFromRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type[a: Integer, b: Integer, ... String]);");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsRecordWithoutRestTypeFromRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type[a: Integer, b: Integer]);");
		$this->assertEquals("@HydrationError![\n\tvalue: [a: 1, b: 2, c: 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'The record value may not contain the key c'\n]", $result);
	}

	public function testHydrateAsRecordWithOptionalKeyFromRecordWithKey(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type[a: Integer, b: Integer, c: ?String]);");
		$this->assertEquals("[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsRecordWithOptionalKeyFromRecordWithoutKey(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->hydrateAs(type[a: Integer, b: Integer, c: ?String]);");
		$this->assertEquals("[a: 1, b: 2]", $result);
	}

	public function testHydrateAsRecordFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type[a: Integer, b: Integer, c: String]);");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a record with 3 items'\n]", $result);
	}








	public function testHydrateAsTupleFromTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type[Integer, Integer, String]);");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsTupleFromTupleWrongPropertyType(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type[Integer, Integer, Real]);");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value[2]',\n\terrorMessage: 'The value should be a real number in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsTupleFromTupleMissingProperty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->hydrateAs(type[Integer, Integer, Real]);");
		$this->assertEquals("@HydrationError![\n\tvalue: [1, 2],\n\thydrationPath: 'value',\n\terrorMessage: 'The tuple value should contain the index 2'\n]", $result);
	}

	public function testHydrateAsTupleWithRestTypeFromTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type[Integer, Integer, ... String]);");
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

	public function testHydrateAsTupleWithoutRestTypeFromTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2, 'hello']->hydrateAs(type[Integer, Integer]);");
		$this->assertEquals("@HydrationError![\n\tvalue: [1, 2, 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'The tuple value should be with 2 items'\n]", $result);
	}

	public function testHydrateAsTupleFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type[Integer, Integer, String]);");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a tuple with 3 items'\n]", $result);
	}



	public function testHydrateAsMutableCorrectType(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Mutable<Real>});");
		$this->assertEquals("mutable{Real, 3.14}", $result);
	}

	public function testHydrateAsMutableCorrectTypeOutOfRange(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Mutable<Real<1..2.5>>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The real value should be in [1..2.5]'\n]", $result);
	}

	public function testHydrateAsMutableWrongType(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Mutable<Integer>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be an integer in (-Infinity..+Infinity)'\n]", $result);
	}



	public function testHydrateAsEnumerationCorrectValue(): void {
		$result = $this->executeCodeSnippet("'A'->hydrateAs(type{MyEnumeration});", "MyEnumeration := (A, B, C);");
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testHydrateAsEnumerationWrongValue(): void {
		$result = $this->executeCodeSnippet("'D'->hydrateAs(type{MyEnumeration});", "MyEnumeration := (A, B, C);");
		$this->assertEquals("@HydrationError![\n\tvalue: 'D',\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a value among MyEnumeration.A, MyEnumeration.B, MyEnumeration.C'\n]", $result);
	}

	public function testHydrateAsEnumerationSubsetCorrectValue(): void {
		$result = $this->executeCodeSnippet("'A'->hydrateAs(type{MyEnumeration[A, C]});", "MyEnumeration := (A, B, C);");
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testHydrateAsEnumerationSubsetWrongSubsetValue(): void {
		$result = $this->executeCodeSnippet("'B'->hydrateAs(type{MyEnumeration[A, C]});", "MyEnumeration := (A, B, C);");
		$this->assertEquals("@HydrationError![\n\tvalue: 'B',\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a value among MyEnumeration.A, MyEnumeration.C'\n]", $result);
	}

	public function testHydrateAsEnumerationSubsetWrongValue(): void {
		$result = $this->executeCodeSnippet("'D'->hydrateAs(type{MyEnumeration[A, C]});", "MyEnumeration := (A, B, C);");
		$this->assertEquals("@HydrationError![\n\tvalue: 'D',\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a value among MyEnumeration.A, MyEnumeration.C'\n]", $result);
	}


	public function testHydrateAsEnumerationWithCastCorrectValue(): void {
		$result = $this->executeCodeSnippet("'AA'->hydrateAs(type{MyEnumeration});",
			"MyEnumeration := (A, B, C); JsonValue ==> MyEnumeration @ String :: ?whenValueOf($) is { 'AA': MyEnumeration.A, 'BB': MyEnumeration.B, 'CC': MyEnumeration.C, ~: @'unknown value' };");
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testHydrateAsEnumerationWithCastWrongValue(): void {
		$result = $this->executeCodeSnippet("'X'->hydrateAs(type{MyEnumeration});",
			"MyEnumeration := (A, B, C); JsonValue ==> MyEnumeration @ String :: ?whenValueOf($) is { 'AA': MyEnumeration.A, 'BB': MyEnumeration.B, 'CC': MyEnumeration.C, ~: @'unknown value' };");
		$this->assertEquals("@HydrationError![\n\tvalue: 'X',\n\thydrationPath: 'value',\n\terrorMessage: 'Enumeration hydration failed. Error: \`unknown value\`'\n]", $result);
	}

	public function testHydrateAsEnumerationSubsetWithCastCorrectValue(): void {
		$result = $this->executeCodeSnippet("'AA'->hydrateAs(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C); JsonValue ==> MyEnumeration @ String :: ?whenValueOf($) is { 'AA': MyEnumeration.A, 'BB': MyEnumeration.B, 'CC': MyEnumeration.C, ~: @'unknown value' };");
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testHydrateAsEnumerationSubsetWithCastWrongSubsetValue(): void {
		$result = $this->executeCodeSnippet("'BB'->hydrateAs(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C); JsonValue ==> MyEnumeration @ String :: ?whenValueOf($) is { 'AA': MyEnumeration.A, 'BB': MyEnumeration.B, 'CC': MyEnumeration.C, ~: @'unknown value' };");
		$this->assertEquals("@HydrationError![\n\tvalue: 'BB',\n\thydrationPath: 'value',\n\terrorMessage: 'The enumeration value MyEnumeration.B is not among MyEnumeration.A, MyEnumeration.C'\n]", $result);
	}

	public function testHydrateAsEnumerationSubsetWithCastWrongValue(): void {
		$result = $this->executeCodeSnippet("'X'->hydrateAs(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C); JsonValue ==> MyEnumeration @ String :: ?whenValueOf($) is { 'AA': MyEnumeration.A, 'BB': MyEnumeration.B, 'CC': MyEnumeration.C, ~: @'unknown value' };");
		$this->assertEquals("@HydrationError![\n\tvalue: 'X',\n\thydrationPath: 'value',\n\terrorMessage: 'Enumeration hydration failed. Error: \`unknown value\`'\n]", $result);
	}



	public function testHydrateAsAliasWithCorrectValue(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{MyInteger});", "MyInteger = Integer<5..100>;");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsAliasWithIncorrectValue(): void {
		$result = $this->executeCodeSnippet("2->hydrateAs(type{MyInteger});", "MyInteger = Integer<5..100>;");
		$this->assertEquals("@HydrationError![\n\tvalue: 2,\n\thydrationPath: 'value',\n\terrorMessage: 'The integer value should be in [5..100]'\n]", $result);
	}



	public function testHydrateAsUnionWithCorrectTypeFirst(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Integer|String});");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsUnionWithCorrectTypeSecond(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{String|Integer});");
		$this->assertEquals("42", $result);
	}

	// TODO - maybe show all errors
	public function testHydrateAsUnionWithIncorrectCorrectType(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{String|Integer});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a length between 0 and +Infinity'\n]", $result);
	}



	public function testHydrateAsResultWithCorrectTypeFirst(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Result<Integer, String>});");
		$this->assertEquals("42", $result);
	}

	public function testHydrateAsResultWithCorrectTypeSecond(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Result<String, Integer>});");
		$this->assertEquals("@42", $result);
	}

	public function testHydrateAsResultWithIncorrectType(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{Result<String, Integer>});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a length between 0 and +Infinity'\n]", $result);
	}

	public function testHydrateAsResultWithCorrectErrorType(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Error<Integer>});");
		$this->assertEquals("@42", $result);
	}

	// TODO - maybe show all errors
	public function testHydrateAsResultWithIncorrectCorrectType(): void {
		$result = $this->executeCodeSnippet("3.14->hydrateAs(type{String|Integer});");
		$this->assertEquals("@HydrationError![\n\tvalue: 3.14,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string with a length between 0 and +Infinity'\n]", $result);
	}




	public function testHydrateAsSealedFromRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String];");
		$this->assertEquals("MySealed[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsSealedFromRecordWrongPropertyType(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: Real];");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value.c',\n\terrorMessage: 'The value should be a real number in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsSealedFromRecordWithValidatorPass(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String] @ Real :: ?when(#a < 0) { => @3.14 };");
		$this->assertEquals("MySealed[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsSealedFromRecordWithValidatorFail(): void {
		$result = $this->executeCodeSnippet("[a: -1, b: 2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String] @ Real :: ?when(#a < 0) { => @3.14 };");
		$this->assertEquals("@HydrationError![\n\tvalue: [a: -1, b: 2, c: 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'Value construction failed. Error: 3.14'\n]", $result);
	}

	public function testHydrateAsSealedFromRecordWithCastCorrectValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String];" .
			"JsonValue ==> MySealed @ String :: ?whenTypeOf($) is { type[a: Integer, b: Integer<0..>, c: String]: MySealed($), ~: @'invalid value' };"
		);
		$this->assertEquals("MySealed[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsSealedFromRecordWithCastIncorrectValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: -2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String];" .
			"JsonValue ==> MySealed @ String :: ?whenTypeOf($) is { type[a: Integer, b: Integer<0..>, c: String]: MySealed($), ~: @'invalid value' };"
		);
		$this->assertEquals("@HydrationError![\n\tvalue: [a: 1, b: -2, c: 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'Sealed type hydration failed. Error: \`invalid value\`'\n]", $result);
	}

	public function testHydrateAsSealedFromRecordWithValidatorAndCastCorrectValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String] @ Real :: ?when(#a < 0) { => @3.14 };" .
			"JsonValue ==> MySealed @ Real|String :: ?whenTypeOf($) is { type[a: Integer, b: Integer<0..>, c: String]: MySealed($), ~: @'invalid value' };"
		);
		$this->assertEquals("MySealed[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsSealedFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{MySealed});",
			"MySealed := $[a: Integer, b: Integer, c: String];");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a record with 3 items'\n]", $result);
	}

	public function testHydrateAsDataFromRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyData});",
			"MyData := #[a: Integer, b: Integer, c: String];");
		$this->assertEquals("MyData[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsDataFromRecordWrongPropertyType(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyData});",
			"MyData := #[a: Integer, b: Integer, c: Real];");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value.c',\n\terrorMessage: 'The value should be a real number in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsOpenFromRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String];");
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsOpenFromRecordWrongPropertyType(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: Real];");
		$this->assertEquals("@HydrationError![\n\tvalue: 'hello',\n\thydrationPath: 'value.c',\n\terrorMessage: 'The value should be a real number in (-Infinity..+Infinity)'\n]", $result);
	}

	public function testHydrateAsOpenFromRecordWithValidatorPass(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String] @ Real :: ?when(#a < 0) { => @3.14 };");
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsOpenFromRecordWithValidatorFail(): void {
		$result = $this->executeCodeSnippet("[a: -1, b: 2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String] @ Real :: ?when(#a < 0) { => @3.14 };");
		$this->assertEquals("@HydrationError![\n\tvalue: [a: -1, b: 2, c: 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'Value construction failed. Error: 3.14'\n]", $result);
	}

	public function testHydrateAsOpenFromRecordWithCastCorrectValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String];" .
			"JsonValue ==> MyOpen @ String :: ?whenTypeOf($) is { type[a: Integer, b: Integer<0..>, c: String]: MyOpen($), ~: @'invalid value' };"
		);
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsOpenFromRecordWithCastIncorrectValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: -2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String];" .
			"JsonValue ==> MyOpen @ String :: ?whenTypeOf($) is { type[a: Integer, b: Integer<0..>, c: String]: MyOpen($), ~: @'invalid value' };"
		);
		$this->assertEquals("@HydrationError![\n\tvalue: [a: 1, b: -2, c: 'hello'],\n\thydrationPath: 'value',\n\terrorMessage: 'Open type hydration failed. Error: \`invalid value\`'\n]", $result);
	}

	public function testHydrateAsOpenFromRecordWithValidatorAndCastCorrectValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 'hello']->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String] @ Real :: ?when(#a < 0) { => @3.14 };" .
			"JsonValue ==> MyOpen @ Real|String :: ?whenTypeOf($) is { type[a: Integer, b: Integer<0..>, c: String]: MyOpen($), ~: @'invalid value' };"
		);
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 'hello']", $result);
	}

	public function testHydrateAsOpenFromOther(): void {
		$result = $this->executeCodeSnippet("null->hydrateAs(type{MyOpen});",
			"MyOpen := #[a: Integer, b: Integer, c: String];");
		$this->assertEquals("@HydrationError![\n\tvalue: null,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a record with 3 items'\n]", $result);
	}

	public function testHydrateAsTypeAny(): void {
		$result = $this->executeCodeSnippet("'Any'->hydrateAs(type{Type});");
		$this->assertEquals("type{Any}", $result);
	}

	public function testHydrateAsTypeNothing(): void {
		$result = $this->executeCodeSnippet("'Nothing'->hydrateAs(type{Type});");
		$this->assertEquals("type{Nothing}", $result);
	}

	public function testHydrateAsTypeArray(): void {
		$result = $this->executeCodeSnippet("'Array'->hydrateAs(type{Type});");
		$this->assertEquals("type{Array}", $result);
	}

	public function testHydrateAsTypeMap(): void {
		$result = $this->executeCodeSnippet("'Map'->hydrateAs(type{Type});");
		$this->assertEquals("type{Map}", $result);
	}

	public function testHydrateAsTypeMutable(): void {
		$result = $this->executeCodeSnippet("'Mutable'->hydrateAs(type{Type});");
		$this->assertEquals("type{Mutable<Any>}", $result);
	}

	public function testHydrateAsTypeType(): void {
		$result = $this->executeCodeSnippet("'Type'->hydrateAs(type{Type});");
		$this->assertEquals("type{Type}", $result);
	}

	public function testHydrateAsTypeNull(): void {
		$result = $this->executeCodeSnippet("'Null'->hydrateAs(type{Type});");
		$this->assertEquals("type{Null}", $result);
	}

	public function testHydrateAsTypeTrue(): void {
		$result = $this->executeCodeSnippet("'True'->hydrateAs(type{Type});");
		$this->assertEquals("type{True}", $result);
	}

	public function testHydrateAsTypeFalse(): void {
		$result = $this->executeCodeSnippet("'False'->hydrateAs(type{Type});");
		$this->assertEquals("type{False}", $result);
	}

	public function testHydrateAsTypeBoolean(): void {
		$result = $this->executeCodeSnippet("'Boolean'->hydrateAs(type{Type});");
		$this->assertEquals("type{Boolean}", $result);
	}

	public function testHydrateAsTypeInteger(): void {
		$result = $this->executeCodeSnippet("'Integer'->hydrateAs(type{Type});");
		$this->assertEquals("type{Integer}", $result);
	}

	public function testHydrateAsTypeReal(): void {
		$result = $this->executeCodeSnippet("'Real'->hydrateAs(type{Type});");
		$this->assertEquals("type{Real}", $result);
	}

	public function testHydrateAsTypeString(): void {
		$result = $this->executeCodeSnippet("'String'->hydrateAs(type{Type});");
		$this->assertEquals("type{String}", $result);
	}

	public function testHydrateAsTypeDefault(): void {
		$result = $this->executeCodeSnippet("'Default'->hydrateAs(type{Type});", "Default = Integer<1..5>;");
		$this->assertEquals("type{Default}", $result);
	}

	public function testHydrateAsTypeInvalidTypeName(): void {
		$result = $this->executeCodeSnippet("'Invalid'->hydrateAs(type{Type});", "Default = Integer<1..5>;");
		$this->assertEquals("@HydrationError![\n\tvalue: 'Invalid',\n\thydrationPath: 'value',\n\terrorMessage: 'The string value should be a name of a valid type'\n]", $result);
	}

	public function testHydrateAsTypeInvalidSourceType(): void {
		$result = $this->executeCodeSnippet("42->hydrateAs(type{Type});");
		$this->assertEquals("@HydrationError![\n\tvalue: 42,\n\thydrationPath: 'value',\n\terrorMessage: 'The value should be a string, containing a name of a valid type'\n]", $result);
	}


}