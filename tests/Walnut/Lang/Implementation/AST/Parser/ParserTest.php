<?php /** @noinspection TypeUnsafeComparisonInspection */

namespace Walnut\Lang\Test\Implementation\AST\Parser;

use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddVariableNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FalseTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FunctionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ImpureTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MapTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MutableTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NamedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NothingTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NullTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ProxyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RecordTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ResultTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\SetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ShapeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\StringTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TrueTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TupleTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\UnionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\EnumerationValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ErrorValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FalseValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\MutableValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\NullValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RealValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RecordValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\SetValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TrueValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TupleValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TypeValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Implementation\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Builder\SourceLocator;
use Walnut\Lang\Implementation\AST\Parser\ParserState;
use Walnut\Lang\Implementation\AST\Parser\ParserStateRunner;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;

class ParserTest extends TestCase {

	protected WalexLexerAdapter $walexLexerAdapter;
	protected ParserStateRunner $parserStateRunner;

	protected function setUp(): void {
		parent::setUp();

		$this->walexLexerAdapter = new WalexLexerAdapter();
		$this->parserStateRunner = new ParserStateRunner(
			new TransitionLogger(),
			new NodeBuilderFactory()
		);
	}

	/** @return array{0: ParserState, 1: ModuleNode} */
	protected function runParserTest(string $sourceCode, int $initialState = 101): array {
		$s = new ParserState;
		$s->push(-1);
		$s->state = $initialState;
		return [$s, $this->parserStateRunner->run(
			new SourceLocator(
				'test',
				$this->walexLexerAdapter->tokensFromSource($sourceCode),
				$s
			)
		)];
	}

	public function testSimple(): void {
		[, $moduleNode] = $this->runParserTest('module test: A = Integer;');
		self::assertEquals('test', $moduleNode->moduleName);
		self::assertCount(0, $moduleNode->moduleDependencies);
		self::assertInstanceOf(AddAliasTypeNode::class, $moduleNode->definitions[0]);
		self::assertEquals('A', $moduleNode->definitions[0]->name->identifier);
		self::assertInstanceOf(IntegerTypeNode::class, $moduleNode->definitions[0]->aliasedType);
	}

	public function testSimpleWithDependencies(): void {
		[, $moduleNode] = $this->runParserTest('module test %% dep1, dep2: A = Integer;');
		self::assertCount(2, $moduleNode->moduleDependencies);
		self::assertEquals('dep1', $moduleNode->moduleDependencies[0]);
		self::assertInstanceOf(AddAliasTypeNode::class, $moduleNode->definitions[0]);
		self::assertEquals('A', $moduleNode->definitions[0]->name->identifier);
		self::assertInstanceOf(IntegerTypeNode::class, $moduleNode->definitions[0]->aliasedType);
	}

	#[DataProvider('moduleLevelDefinitions')]
	public function testModuleLevelDefinitions(string $code, string $className, callable|null $checker = null): void {
		[$s, $moduleNode] = $this->runParserTest($code, 102);
		self::assertInstanceOf($className, $moduleNode->definitions[0]);
		if (is_callable($checker)) {
			self::assertTrue($checker($moduleNode->definitions[0]));
		}
	}


	public static function moduleLevelDefinitions(): iterable {
		yield ['MyAtom = :[];', AddAtomTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyAtom'))];
		yield ['MyEnum = :[v1, v2];', AddEnumerationTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyEnum')) &&
			count($d->values) === 2 && $d->values[0]->equals(new EnumValueIdentifier('v1')) && $d->values[1]->equals(new EnumValueIdentifier('v2'))];
		yield ['MyOpen = #Null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MyOpen = #Null :: null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType === null];
		yield ['MyOpen = #Null @ E :: null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameIdentifier('E'))];
		yield ['MyOpen = #[Real];', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf TupleTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MySealed = $Null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MySealed = $Null :: null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType === null];
		yield ['MySealed = $Null @ E :: null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameIdentifier('E'))];
		yield ['MySealed = $[a: Integer];', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf RecordTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MyAlias = Null;', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyAlias')) &&
			$d->aliasedType instanceOf NullTypeNode];
		yield ['myVar = null;', AddVariableNode::class, fn($d) => $d->name->equals(new VariableNameIdentifier('myVar')) &&
			$d->value instanceOf NullValueNode];

		yield ['A(P) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) %% D :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) @ C :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameIdentifier('C'))];
		yield ['A(P) @ C %% D :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameIdentifier('C'))];
		yield ['A(~P) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(p: P) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(p) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof AnyTypeNode && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A() :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof TupleTypeNode && count($d->parameterType->types) === 0 && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[P] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof TupleTypeNode && count($d->parameterType->types) === 1 &&
			$d->parameterType->types[0] instanceof NamedTypeNode && $d->parameterType->types[0]->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[P, Q] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof TupleTypeNode && count($d->parameterType->types) === 2 &&
			$d->parameterType->types[0] instanceof NamedTypeNode && $d->parameterType->types[0]->name->equals(new TypeNameIdentifier('P')) &&
			$d->parameterType->types[1] instanceof NamedTypeNode && $d->parameterType->types[1]->name->equals(new TypeNameIdentifier('Q')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[:] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof RecordTypeNode && count($d->parameterType->types) === 0 && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[a: P] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof RecordTypeNode && count($d->parameterType->types) === 1 &&
			$d->parameterType->types['a'] instanceof NamedTypeNode && $d->parameterType->types['a']->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[a: P, b: Q] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameIdentifier('A')) &&
			$d->parameterType instanceof RecordTypeNode && count($d->parameterType->types) === 2 &&
			$d->parameterType->types['a'] instanceof NamedTypeNode && $d->parameterType->types['a']->name->equals(new TypeNameIdentifier('P')) &&
			$d->parameterType->types['b'] instanceof NamedTypeNode && $d->parameterType->types['b']->name->equals(new TypeNameIdentifier('Q')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];



		yield ['A->asB(^P) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^P) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^P => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A->asB(^P => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];


		yield ['A->asB(^ ~P) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^ ~P) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^ ~P => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A->asB(^ ~P => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];


		yield ['A->asB(^p: P) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^p: P) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^p: P => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A->asB(^p: P => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NamedTypeNode && $d->parameterType->name->equals(new TypeNameIdentifier('P')) && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];


		yield ['A->asB(^p) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof AnyTypeNode && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^p) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof AnyTypeNode && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^p => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof AnyTypeNode && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A->asB(^p => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof AnyTypeNode && $d->parameterName->equals(new VariableNameIdentifier('p')) &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];


		yield ['A->asB() :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB() %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(=> B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A->asB(=> Result<B, C>) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameIdentifier('B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameIdentifier('C'))
		];
		yield ['A->asB(=> B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A->asB(=> Result<B, C>) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameIdentifier('B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameIdentifier('C'))
		];

		yield ['A ==> B :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A ==> B @ C :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameIdentifier('B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameIdentifier('C'))
		];
		yield ['A ==> B %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['A ==> B @ C %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('A')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameIdentifier('B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameIdentifier('C'))
		];

		yield ['==> B :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('DependencyContainer')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['==> B @ C :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('DependencyContainer')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NothingTypeNode &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameIdentifier('B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameIdentifier('C'))
		];
		yield ['==> B %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('DependencyContainer')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B'))];
		yield ['==> B @ C %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('DependencyContainer')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameIdentifier('B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameIdentifier('C'))
		];
	}

	#[DataProvider('types')]
	public function testParseTypes(string $code, string $className, callable|null $checker = null): void {
		[$s] = $this->runParserTest($code, 701);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
	}

	public static function types(): iterable {
		yield ['Nothing', NothingTypeNode::class];
		yield ['!Nothing', NothingTypeNode::class];
		yield ['True', TrueTypeNode::class];
		yield ['!True', TrueTypeNode::class];
		yield ['False', FalseTypeNode::class];
		yield ['!False', FalseTypeNode::class];
		yield ['Boolean', BooleanTypeNode::class];
		yield ['!Boolean', BooleanTypeNode::class];
		yield ['Any', AnyTypeNode::class];
		yield ['!Any', AnyTypeNode::class];
		yield ['Integer', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['!Integer', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['Integer<5..>', IntegerTypeNode::class, fn($t) => $t->minValue == new Number('5') && $t->maxValue === PlusInfinity::value];
		yield ['Integer<..-8>', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8')];
		yield ['Integer<-5..8>', IntegerTypeNode::class, fn($t) => $t->minValue == new Number('-5') && $t->maxValue == new Number('8')];
		yield ['Integer[5]', IntegerSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0] == new Number('5')];
		yield ['Integer[5, -8]', IntegerSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0] == new Number('5') && $t->values[1] == new Number('-8')];
		yield ['Real', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['!Real', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['Real<5..>', RealTypeNode::class, fn($t) => $t->minValue == new Number('5') && $t->maxValue === PlusInfinity::value];
		yield ['Real<..-8>', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8')];
		yield ['Real<-5..8>', RealTypeNode::class, fn($t) => $t->minValue == new Number('-5') && $t->maxValue == new Number('8')];
		yield ['Real<5.14..>', RealTypeNode::class, fn($t) => $t->minValue == new Number('5.14') && $t->maxValue === PlusInfinity::value];
		yield ['Real<..-8.14>', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8.14')];
		yield ['Real<-5.14..8.14>', RealTypeNode::class, fn($t) => $t->minValue == new Number('-5.14') && $t->maxValue == new Number('8.14')];
		yield ['Real[3.14]', RealSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0] == new Number('3.14')];
		yield ['Real[3.14, -8]', RealSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0] == new Number('3.14') && $t->values[1] == new Number('-8')];
		yield ['String', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['!String', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['String<5..>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['String<..8>', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['String<5>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['String<5..8>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ["String['hello']", StringSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0] == 'hello'];
		yield ["String['hello', 'world']", StringSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0] == 'hello' && $t->values[1] == 'world'];
		yield ['Array', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['!Array', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Array<5..>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Array<..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Array<5..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Array<Boolean>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Array<Boolean, 5..>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Array<Boolean, ..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Array<Boolean, 5..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Map', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['!Map', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Map<5..>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Map<..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Map<5..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Map<Boolean>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Map<Boolean, 5..>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Map<Boolean, ..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Map<Boolean, 5..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Set', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['!Set', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Set<5..>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Set<..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Set<5..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Set<Boolean>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Set<Boolean, 5..>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Set<Boolean, ..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Set<Boolean, 5..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];

		yield ['MyCustomType', NamedTypeNode::class, fn($t) => $t->name->equals(new TypeNameIdentifier('MyCustomType'))];
		yield ['!MyProxyType', ProxyTypeNode::class, fn($t) => $t->name->equals(new TypeNameIdentifier('MyProxyType'))];
		yield ['Type', TypeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['!Type', TypeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['Type<Boolean>', TypeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['Type<Tuple>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::Tuple];
		yield ['Type<!EnumerationValue>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::EnumerationValue];
		yield ['Type<!MutableValue>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::MutableValue];
		yield ['Type<Integer|Boolean>', TypeTypeNode::class, fn($t) => $t->refType instanceOf UnionTypeNode &&
			$t->refType->left instanceOf IntegerTypeNode && $t->refType->right instanceOf BooleanTypeNode];
		yield ['Mutable', MutableTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['!Mutable', MutableTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['Mutable<Boolean>', MutableTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['Shape', ShapeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['!Shape', ShapeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['Shape<Boolean>', ShapeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['Result', ResultTypeNode::class, fn($t) => $t->returnType instanceOf AnyTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['!Result', ResultTypeNode::class, fn($t) => $t->returnType instanceOf AnyTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Result<Null>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NullTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Result<Null, Boolean>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NullTypeNode && $t->errorType instanceOf BooleanTypeNode];
		yield ['Error', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['!Error', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Error<Boolean>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf BooleanTypeNode];
		yield ['Impure', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['!Impure', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['Impure<Boolean>', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['*Boolean', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['[]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf NothingTypeNode];
		yield ['[...]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf AnyTypeNode];
		yield ['[... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf BooleanTypeNode];
		yield ['[Null]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[Null, ...]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[Null, ... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[Null, Any]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[Null, Any, ...]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[Null, Any, ... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[:]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf NothingTypeNode];
		yield ['[: ...]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf AnyTypeNode];
		yield ['[: ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null, ...]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[a: Null, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null, b: Any]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null, b: Any, ...]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[a: Null, b: Any, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null, b: Any, c: ?Null, d: OptionalKey<Null>, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 4 &&
			$t->types['a'] instanceof NullTypeNode &&
			$t->types['b'] instanceof AnyTypeNode &&
			$t->types['c'] instanceof OptionalKeyTypeNode && $t->types['c']->valueType instanceof NullTypeNode &&
			$t->types['d'] instanceof OptionalKeyTypeNode && $t->types['d']->valueType instanceof NullTypeNode &&
			$t->restType instanceOf BooleanTypeNode];
		yield ["['a': Null, 'b': Any, ... Boolean]", RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];

		yield ['MyEnum[Value1]', EnumerationSubsetTypeNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyEnum')) && count($v->values) === 1 &&
			$v->values[0]->equals(new EnumValueIdentifier('Value1'))];
		yield ['MyEnum[Value1, Value2]', EnumerationSubsetTypeNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyEnum')) && count($v->values) === 2 &&
			$v->values[0]->equals(new EnumValueIdentifier('Value1')) && $v->values[1]->equals(new EnumValueIdentifier('Value2'))];
		yield ['^Null => Any', FunctionTypeNode::class, fn($v) => $v->parameterType instanceOf NullTypeNode && $v->returnType instanceOf AnyTypeNode];
		yield ['Boolean|Null', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['(Boolean|Null)', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['Boolean|Null|Type', UnionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['(Boolean|Null|Type)', UnionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['Boolean&Null', IntersectionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['(Boolean&Null)', IntersectionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['Boolean&Null&Type', IntersectionTypeNode::class, fn($t) => $t->left instanceof IntersectionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['(Boolean&Null&Type)', IntersectionTypeNode::class, fn($t) => $t->left instanceof IntersectionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['Boolean|(Null&Type)', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof IntersectionTypeNode &&
			$t->right->left instanceof NullTypeNode && $t->right->right instanceof TypeTypeNode];
		yield ['Boolean&(Null|Type)', IntersectionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof UnionTypeNode &&
			$t->right->left instanceof NullTypeNode && $t->right->right instanceof TypeTypeNode];

		//yield ["['a': Null, 'b': Any, ... Boolean]", RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		//yield ['OptionalKey', OptionalKeyTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		//yield ['OptionalKey<Boolean>', OptionalKeyTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
	}

	#[DataProvider('constants')]
	public function testParseConstants(string $code, string $className, callable|null $checker = null): void {
		[$s] = $this->runParserTest($code, 401);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
	}

	public static function constants(): iterable {
		yield ['42', IntegerValueNode::class, fn($v) => $v->value == new Number('42')];
		yield ['3.14', RealValueNode::class, fn($v) => $v->value == new Number('3.14')];
		yield ["'Hello'", StringValueNode::class, fn($v) => $v->value == 'Hello'];
		yield ['true', TrueValueNode::class];
		yield ['false', FalseValueNode::class];
		yield ['null', NullValueNode::class];
		yield ['[]', TupleValueNode::class, fn($v) => count($v->values) == 0];
		yield ['[42]', TupleValueNode::class, fn($v) => count($v->values) == 1 && $v->values[0]->value == new Number('42')];
		yield ["['a']", TupleValueNode::class, fn($v) => count($v->values) == 1 && $v->values[0]->value == 'a'];
		yield ['[42, 3.14]', TupleValueNode::class, fn($v) => count($v->values) == 2 && $v->values[0]->value == new Number('42') && $v->values[1]->value == new Number('3.14')];
		yield ['[:]', RecordValueNode::class, fn($v) => count($v->values) == 0];
		yield ['[a: 42]', RecordValueNode::class, fn($v) => count($v->values) == 1 && $v->values['a']->value == new Number('42')];
		yield ["[a: 42, 'b': 3.14]", RecordValueNode::class, fn($v) => count($v->values) == 2 && $v->values['a']->value == new Number('42') && $v->values['b']->value == new Number('3.14')];
		yield ["['a': 42, b: 3.14]", RecordValueNode::class, fn($v) => count($v->values) == 2 && $v->values['a']->value == new Number('42') && $v->values['b']->value == new Number('3.14')];
		yield ['[;]', SetValueNode::class, fn($v) => count($v->values) == 0];
		yield ['[42;]', SetValueNode::class, fn($v) => count($v->values) == 1 && $v->values[0]->value == new Number('42')];
		yield ['[42; 3.14]', SetValueNode::class, fn($v) => count($v->values) == 2 && $v->values[0]->value == new Number('42') && $v->values[1]->value == new Number('3.14')];
		yield ['@null', ErrorValueNode::class, fn($v) => $v->value instanceof NullValueNode];
		yield ['mutable{Integer, 0}', MutableValueNode::class, fn($v) => $v->type instanceof IntegerTypeNode && $v->value instanceof IntegerValueNode];
		yield ['type{Any}', TypeValueNode::class, fn($v) => $v->type instanceof AnyTypeNode];
		yield ['type{Atom}', TypeValueNode::class, fn($v) => $v->type instanceof MetaTypeTypeNode && $v->type->value === MetaTypeValue::Atom];
		yield ['type{[Any]}', TypeValueNode::class, fn($v) => $v->type instanceof TupleTypeNode && count($v->type->types) === 1 && $v->type->types[0] instanceof AnyTypeNode];
		yield ['type[Any]', TypeValueNode::class, fn($v) => $v->type instanceof TupleTypeNode && count($v->type->types) === 1 && $v->type->types[0] instanceof AnyTypeNode];
		yield ['`Any', TypeValueNode::class, fn($v) => $v->type instanceof AnyTypeNode];
		yield ['`Atom', TypeValueNode::class, fn($v) => $v->type instanceof MetaTypeTypeNode && $v->type->value === MetaTypeValue::Atom];
		yield ['`[Any]', TypeValueNode::class, fn($v) => $v->type instanceof TupleTypeNode && count($v->type->types) === 1 && $v->type->types[0] instanceof AnyTypeNode];
		yield ['MyAtom()', AtomValueNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyAtom'))];
		yield ['MyEnum.Value', EnumerationValueNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyEnum')) && $v->enumValue->equals(new EnumValueIdentifier('Value'))];
		yield ['^ :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^ => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^p :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^p => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^ ~P => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof NamedTypeNode &&
									$v->parameterType->name->equals(new TypeNameIdentifier('P')) && $v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^ ~P :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof NamedTypeNode &&
									$v->parameterType->name->equals(new TypeNameIdentifier('P')) && $v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^p: Q => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof NamedTypeNode &&
									$v->parameterType->name->equals(new TypeNameIdentifier('Q')) && $v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^p: Q :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof NamedTypeNode &&
									$v->parameterType->name->equals(new TypeNameIdentifier('Q')) && $v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^ => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^Null => Any %% True :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof TrueTypeNode];
		yield ['^p: False => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof FalseTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^[Any] :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof TupleTypeNode &&
									count($v->parameterType->types) === 1 && $v->parameterType->types[0] instanceof AnyTypeNode && $v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
	}

}