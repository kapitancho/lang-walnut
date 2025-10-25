<?php

namespace Walnut\Lang\Test\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ShapeTypeTest extends CodeExecutionTestHelper {


	public function testAsStringShape(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->asString;",
			valueDeclarations: "getReal = ^ => Shape<Real> :: 3.14;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringShapeParam(): void {
		$result = $this->executeCodeSnippet("getValue(getReal());",
			valueDeclarations: "
				getValue = ^ r: Shape<Real> => Real :: r->shape(`Real);
				getReal = ^ => Shape<Real> :: 3.14;
			"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testShapeWithCastError(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"getValue(getString());",
			valueDeclarations: "
				getString = ^ => String :: '7 days';
				getValue = ^ r: Shape<Real> => Real :: r->shape;
			"
		);
	}

	public function testAsStringShapeParamWithCast(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->ceil;",
			"
				MyReal := #[value: Real];
				MyReal ==> Real :: \$value;
			",
			"
				getValue = ^ r: Shape<Real> => Real :: r->shape(`Real);
				getReal = ^ => Shape<Real> :: MyReal[3.14];
			"
		);
		$this->assertEquals("4", $result);
	}

	public function testShapeAsRefinedType(): void {
		$result = $this->executeCodeSnippet("useReal(getReal());",
			valueDeclarations: "
				useReal = ^p: Any => Any :: ?whenTypeOf(p) is {
					type{Shape<Real>}: p->shape(`Real)->ceil,
					type{String}: p
				};
				getReal = ^ => Shape<Real> :: 3.14;
			"
		);
		$this->assertEquals("4", $result);
	}

	public function testX1(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: Shape<[a: Real]> => Real :: v->shape(`[a: Real]).a;
		NUT);
		$this->assertEquals("3.14", $result);
	}

	public function testX2(): void {
		$this->executeErrorCodeSnippet(
			"Cannot convert value of type 'Shape<[a: Real]>' to shape '[a: Integer]'",
			"useB(getA());",
		valueDeclarations:  <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: Shape<[a: Real]> => Real :: v->shape(`[a: Integer]).a;
		NUT);
	}

	public function testX3(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Integer] :: [a: 42];	
			useB = ^v: Shape<[a: Integer]> => Real :: v->shape(`[a: Real]).a;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testX4(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: [a: Real] => Real :: v->shape(`[a: Real]).a;
		NUT);
		$this->assertEquals("3.14", $result);
	}

	public function testX5(): void {
		$this->executeErrorCodeSnippet(
			"Cannot convert value of type '[a: Real, ... String]' to shape '[a: Real]'",
			"useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real, ... String] :: [a: 3.14];	
			useB = ^v: [a: Real, ... String] => Real :: v->shape(`[a: Real]).a;
		NUT);
	}

	public function testX6(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: [a: Real] => Real :: v->shape(`[a: Real, ... String]).a;
		NUT);
		$this->assertEquals("3.14", $result);
	}

	public function testX14(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => Integer :: 42;	
			useB = ^v: Integer => Integer :: v->shape(`Real)->ceil;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testX15(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => Real :: 3.14;	
			useB = ^v: Real => Array<Integer> :: v->shape(`Integer)->upTo(5);
		NUT);
		$this->assertEquals("[3, 4, 5]", $result);
	}
}