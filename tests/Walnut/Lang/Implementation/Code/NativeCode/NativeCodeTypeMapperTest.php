<?php

namespace Walnut\Lang\Test\Implementation\Code\NativeCode;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Test\BaseProgramTestHelper;

class NativeCodeTypeMapperTest extends BaseProgramTestHelper {

	private NativeCodeTypeMapper $nativeCodeTypeMapper;

	protected function setUp(): void {
		parent::setUp();
		
		$this->nativeCodeTypeMapper = new NativeCodeTypeMapper();
	}

	public function testAny(): void {
		$this->assertEquals(
			['Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->any)
		);
	}

	public function testNothing(): void {
		$this->assertEquals(
			['Nothing', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->nothing)
		);
	}

	public function testNull(): void {
		$this->assertEquals(
			['Null', 'Atom', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->null)
		);
	}

	public function testBoolean(): void {
		$this->assertEquals(
			['Boolean', 'Enumeration', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->boolean)
		);
	}

	public function testTrue(): void {
		$this->assertEquals(
			['Boolean', 'Enumeration', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->true)
		);
	}

	public function testFalse(): void {
		$this->assertEquals(
			['Boolean', 'Enumeration', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->false)
		);
	}

	public function testInteger(): void {
		$this->assertEquals(
			['Integer', 'Real', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->integer())
		);
	}

	public function testIntegerSubset(): void {
		$this->assertEquals(
			['Integer', 'Real', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->integerSubset([new Number('1')]))
		);
	}

	public function testReal(): void {
		$this->assertEquals(
			['Real', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->real())
		);
	}

	public function testRealSubset(): void {
		$this->assertEquals(
			['Real', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->realSubset([new Number('3.14')]))
		);
	}

	public function testString(): void {
		$this->assertEquals(
			['String', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->string())
		);
	}

	public function testStringSubset(): void {
		$this->assertEquals(
			['String', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->stringSubset(['hello']))
		);
	}

	public function testArray(): void {
		$this->assertEquals(
			['Array', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->array())
		);
	}

	public function testMap(): void {
		$this->assertEquals(
			['Map', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->map())
		);
	}

	public function testSet(): void {
		$this->assertEquals(
			['Set', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->set())
		);
	}

	public function testTuple(): void {
		$this->assertEquals(
			['Tuple', 'Array', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->tuple([]))
		);
	}

	public function testRecord(): void {
		$this->assertEquals(
			['Record', 'Map', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->record([]))
		);
	}

	public function testUnion(): void {
		$this->assertEquals(
			['Union', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->typeByName(new TypeNameIdentifier('NotANumber'))
			]))
		);
	}

	public function testUnionJsonValue(): void {
		$this->assertEquals(
			['Union', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string()
			]))
		);
	}

	public function testIntersection(): void {
		$this->assertEquals(
			['Intersection', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->intersection([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string()
			]))
		);
	}

	public function testFunction(): void {
		$this->assertEquals(
			['Function', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->function(
				$this->typeRegistry->integer(),
				$this->typeRegistry->string()
			))
		);
	}

	public function testMutable(): void {
		$this->assertEquals(
			['Mutable', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->mutable(
				$this->typeRegistry->typeByName(new TypeNameIdentifier('NotANumber')),
			))
		);
	}

	public function testMutableJsonValue(): void {
		$this->assertEquals(
			['Mutable', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->mutable(
				$this->typeRegistry->integer(),
			))
		);
	}

	public function testOptionalKey(): void {
		$this->assertEquals(
			['Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->optionalKey(
				$this->typeRegistry->integer(),
			))
		);
	}

	public function testImpure(): void {
		$this->assertEquals(
			['Result', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->impure(
				$this->typeRegistry->integer(),
			))
		);
	}

	public function testResult(): void {
		$this->assertEquals(
			['Result', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->result(
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
			))
		);
	}

	public function testAlias(): void {
		$this->assertEquals(
			['DatabaseValue', 'Alias', 'Union', 'JsonValue', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->alias(
				new TypeNameIdentifier('DatabaseValue')
			))
		);
	}

	public function testSubtype(): void {
		$this->assertEquals(
			['DatabaseConnection', 'Subtype', 'Record', 'Map', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->subtype(
				new TypeNameIdentifier('DatabaseConnection')
			))
		);
	}

	public function testSealed(): void {
		$this->assertEquals(
			['HydrationError', 'Sealed', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->sealed(
				new TypeNameIdentifier('HydrationError')
			))
		);
	}

	public function testAtom(): void {
		$this->assertEquals(
			['NotANumber', 'Atom', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->atom(
				new TypeNameIdentifier('NotANumber')
			))
		);
	}

	public function testEnumeration(): void {
		$this->assertEquals(
			['DependencyContainerErrorType', 'Enumeration', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->enumeration(
				new TypeNameIdentifier('DependencyContainerErrorType')
			))
		);
	}

	public function testEnumerationSubset(): void {
		$this->assertEquals(
			['Enumeration', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->enumerationSubsetType(
				new TypeNameIdentifier('DependencyContainerErrorType'), [
				new EnumValueIdentifier('NotFound')
			]))
		);
	}

	public function testType(): void {
		$this->assertEquals(
			['Type', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->type(
				$this->typeRegistry->any,
			))
		);
	}

	public function testTypeFunction(): void {
		$this->assertEquals(
			['Function', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Function
			))
		);
	}

	public function testTypeTuple(): void {
		$this->assertEquals(
			['Tuple', 'Array', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Tuple
			))
		);
	}

	public function testTypeRecord(): void {
		$this->assertEquals(
			['Record', 'Map', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Record
			))
		);
	}

	public function testTypeUnion(): void {
		$this->assertEquals(
			['Union', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Union
			))
		);
	}

	public function testTypeIntersection(): void {
		$this->assertEquals(
			['Intersection', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Intersection
			))
		);
	}

	public function testTypeAtom(): void {
		$this->assertEquals(
			[/*'Atom', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Atom
			))
		);
	}

	public function testTypeEnumeration(): void {
		$this->assertEquals(
			[/*'Enumeration', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Enumeration
			))
		);
	}

	public function testTypeEnumerationSubset(): void {
		$this->assertEquals(
			[/*'EnumerationSubset', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::EnumerationSubset
			))
		);
	}

	public function testTypeIntegerSubset(): void {
		$this->assertEquals(
			[/*'IntegerSubset', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::IntegerSubset
			))
		);
	}

	public function testTypeMutableValue(): void {
		$this->assertEquals(
			['Mutable', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::MutableValue
			))
		);
	}

	public function testTypeRealSubset(): void {
		$this->assertEquals(
			[/*'RealSubset', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::RealSubset
			))
		);
	}

	public function testTypeStringSubset(): void {
		$this->assertEquals(
			[/*'StringSubset', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::StringSubset
			))
		);
	}

	public function testTypeAlias(): void {
		$this->assertEquals(
			[/*'Alias', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Alias
			))
		);
	}

	public function testTypeSubtype(): void {
		$this->assertEquals(
			['Subtype', 'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Subtype
			))
		);
	}

	public function testTypeSealed(): void {
		$this->assertEquals(
			[/*'Sealed', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Sealed
			))
		);
	}

	public function testTypeNamed(): void {
		$this->assertEquals(
			[/*'Named', */'Any'],
			$this->nativeCodeTypeMapper->getTypesFor($this->typeRegistry->metaType(
				MetaTypeValue::Named
			))
		);
	}
}