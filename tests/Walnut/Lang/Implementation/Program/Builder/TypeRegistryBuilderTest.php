<?php

namespace Walnut\Lang\Test\Implementation\Program\Builder;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;

class TypeRegistryBuilderTest extends TestCase {

	private TypeRegistryBuilder $typeRegistryBuilder;

	protected function setUp(): void {
		parent::setUp();

		$this->typeRegistryBuilder = new TypeRegistryBuilder(
			$this->createMock(CustomMethodRegistryBuilder::class),
			$this->createMock(MethodFinder::class),
			new StringEscapeCharHandler(),
		);
		$this->typeRegistryBuilder->addSealed(
			new TypeNameIdentifier('ExternalError'),
			$this->typeRegistryBuilder->null
		); // Fake reference
	}


	public function testTypeByName(): void {
		$b = $this->typeRegistryBuilder;
		$this->assertInstanceOf(AnyType::class, $b->typeByName(new TypeNameIdentifier('Any')));
		$this->assertInstanceOf(NothingType::class, $b->typeByName(new TypeNameIdentifier('Nothing')));
		$this->assertInstanceOf(ArrayType::class, $b->typeByName(new TypeNameIdentifier('Array')));
		$this->assertInstanceOf(ResultType::class, $b->typeByName(new TypeNameIdentifier('Error')));
		$this->assertInstanceOf(MapType::class, $b->typeByName(new TypeNameIdentifier('Map')));
		$this->assertInstanceOf(ResultType::class, $b->typeByName(new TypeNameIdentifier('Impure')));
		$this->assertInstanceOf(MutableType::class, $b->typeByName(new TypeNameIdentifier('Mutable')));
		$this->assertInstanceOf(TypeType::class, $b->typeByName(new TypeNameIdentifier('Type')));
		$this->assertInstanceOf(NullType::class, $b->typeByName(new TypeNameIdentifier('Null')));
		$this->assertInstanceOf(TrueType::class, $b->typeByName(new TypeNameIdentifier('True')));
		$this->assertInstanceOf(FalseType::class, $b->typeByName(new TypeNameIdentifier('False')));
		$this->assertInstanceOf(BooleanType::class, $b->typeByName(new TypeNameIdentifier('Boolean')));
		$this->assertInstanceOf(IntegerType::class, $b->typeByName(new TypeNameIdentifier('Integer')));
		$this->assertInstanceOf(RealType::class, $b->typeByName(new TypeNameIdentifier('Real')));
		$this->assertInstanceOf(StringType::class, $b->typeByName(new TypeNameIdentifier('String')));
		$this->assertInstanceOf(ShapeType::class, $b->typeByName(new TypeNameIdentifier('Shape')));
		$this->assertInstanceOf(MetaType::class, $b->typeByName(new TypeNameIdentifier('Atom')));
		$this->assertInstanceOf(MetaType::class, $b->typeByName(new TypeNameIdentifier('Open')));
		$this->assertInstanceOf(MetaType::class, $b->typeByName(new TypeNameIdentifier('Sealed')));
		$this->assertInstanceOf(MetaType::class, $b->typeByName(new TypeNameIdentifier('Enumeration')));
	}

}