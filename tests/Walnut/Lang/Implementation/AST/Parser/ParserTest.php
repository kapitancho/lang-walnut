<?php /** @noinspection TypeUnsafeComparisonInspection */

namespace Walnut\Lang\Test\Implementation\AST\Parser;

use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\SetExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\Node;
use Walnut\Lang\Blueprint\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FalseTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FunctionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ImpureTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerFullTypeNode;
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
use Walnut\Lang\Blueprint\AST\Node\Type\RealFullTypeNode;
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
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
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
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;

class ParserTest extends TestCase {

	protected WalexLexerAdapter $walexLexerAdapter;
	protected ParserStateRunner $parserStateRunner;
	protected TransitionLogger $transitionLogger;

	protected function setUp(): void {
		parent::setUp();

		$this->walexLexerAdapter = new WalexLexerAdapter();
		$this->parserStateRunner = new ParserStateRunner(
			$this->transitionLogger = new TransitionLogger(),
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

	public function testParseError(): void {
		try {
			$this->runParserTest('module test: A = Integer{;');
			$this->fail("There should be a parser error for this code.");
		} catch (ParserException $ex) {
			$this->assertStringContainsString('No transition found', $ex->getMessage());
		}
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
		$this->checkPosition($code, $s->generated);
	}

	public static function moduleLevelDefinitions(): iterable {
		yield ['MyAtom := ();', AddAtomTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyAtom'))];
		yield ['MyEnum := (v1, v2);', AddEnumerationTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyEnum')) &&
			count($d->values) === 2 && $d->values[0]->equals(new EnumValueIdentifier('v1')) && $d->values[1]->equals(new EnumValueIdentifier('v2'))];
		yield ['MyOpen := #Null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MyOpen := #Null :: null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType === null];
		yield ['MyOpen := #Null @ E :: null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameIdentifier('E'))];
		yield ['MyOpen := #[Real];', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyOpen')) &&
			$d->valueType instanceOf TupleTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MySealed := $Null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MySealed := $Null :: null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType === null];
		yield ['MySealed := $Null @ E :: null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameIdentifier('E'))];
		yield ['MySealed := $[a: Integer];', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MySealed')) &&
			$d->valueType instanceOf RecordTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MyAlias = Null;', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyAlias')) &&
			$d->aliasedType instanceOf NullTypeNode];
		yield ['MyAlias = [];', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyAlias')) &&
			$d->aliasedType instanceOf TupleTypeNode && count($d->aliasedType->types) === 0];
		yield ['MyAlias = [:];', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameIdentifier('MyAlias')) &&
			$d->aliasedType instanceOf RecordTypeNode && count($d->aliasedType->types) === 0];

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
		yield ['==> B %% D;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('DependencyContainer')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B')) &&
			$d->functionBody->expression instanceof MethodCallExpressionNode
		];
		yield ['==> B %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof NamedTypeNode && $d->targetType->name->equals(new TypeNameIdentifier('DependencyContainer')) &&
			$d->methodName->equals(new MethodNameIdentifier('asB')) &&
			$d->parameterType instanceof NullTypeNode && $d->parameterName === null &&
			$d->dependencyType instanceof NamedTypeNode && $d->dependencyType->name->equals(new TypeNameIdentifier('D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameIdentifier('B')) &&
			$d->functionBody->expression instanceof ConstantExpressionNode &&
			$d->functionBody->expression->value instanceof NullValueNode
		];
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

	private function checkPosition(string $code, Node $node): void {
		$n2 = strlen($code);
		$n1 = $n2 - 1;
		$f = $node->sourceLocation->startPosition->offset;
		$t = $node->sourceLocation->endPosition->offset;
		$codeX = $f >= $t ? $code :
			substr($code, 0, $f) .
			"\033[35;1;4m" . substr($code, $f, $t - $f) . "\033[0m" .
			substr($code, $t);
		self::assertEquals(
			"test(1:1:0-1:$n2:$n1)",
			$node->sourceLocation->jsonSerialize(),
			sprintf("The position of %s is not correct.", $codeX)
		);
	}

	#[DataProvider('expressions')]
	public function testParseExpressions(string $code, string $className, callable|null $checker = null): void {
		[$s] = $this->runParserTest($code, 201);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
		//$this->checkPosition($code, $s->generated);
	}

	public static function expressions(): iterable {
		yield ['[]', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof TupleValueNode && count($e->value->values) === 0
		];
		yield ['[;]', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof SetValueNode && count($e->value->values) === 0
		];
		yield ['[:]', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof RecordValueNode && count($e->value->values) === 0
		];
		yield ['4', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof IntegerValueNode && (string)$e->value->value === '4'];
		yield ['C.value', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof EnumerationValueNode && $e->value->name->equals(new TypeNameIdentifier('C')) &&
			$e->value->enumValue->equals(new EnumValueIdentifier('value'))
		];
		yield ['x', VariableNameExpressionNode::class, fn(VariableNameExpressionNode $e) => $e->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['$', VariableNameExpressionNode::class, fn(VariableNameExpressionNode $e) => $e->variableName->equals(new VariableNameIdentifier('$'))];
		yield ['x = y', VariableAssignmentExpressionNode::class, fn(VariableAssignmentExpressionNode $e) =>
			$e->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['mutable{A, x}', MutableExpressionNode::class, fn(MutableExpressionNode $e) =>
			$e->type instanceof NamedTypeNode && $e->type->name->equals(new TypeNameIdentifier('A')) &&
			$e->value instanceof VariableNameExpressionNode && $e->value->variableName->equals(new VariableNameIdentifier('x'))
		];
		yield ['{x; y}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 2 &&
			$e->expressions[0] instanceof VariableNameExpressionNode && $e->expressions[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->expressions[1] instanceof VariableNameExpressionNode && $e->expressions[1]->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['{x}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 1 &&
			$e->expressions[0] instanceof VariableNameExpressionNode && $e->expressions[0]->variableName->equals(new VariableNameIdentifier('x'))
		];
		yield ['{x;}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 1 &&
			$e->expressions[0] instanceof VariableNameExpressionNode && $e->expressions[0]->variableName->equals(new VariableNameIdentifier('x'))
		];
		yield ['{}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 1 &&
			$e->expressions[0] instanceof SequenceExpressionNode
		];
		yield ['[x, y]', TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->values[1] instanceof VariableNameExpressionNode && $e->values[1]->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['[$]', TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameIdentifier('$'))
		];
		yield ['[x]', TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameIdentifier('x'))
		];
		yield ["['x'];", TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof ConstantExpressionNode && $e->values[0]->value instanceof StringValueNode && $e->values[0]->value->value === 'x'
		];
		yield ["['x', 'y'];", TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values[0] instanceof ConstantExpressionNode && $e->values[0]->value instanceof StringValueNode && $e->values[0]->value->value === 'x' &&
			$e->values[1] instanceof ConstantExpressionNode && $e->values[1]->value instanceof StringValueNode && $e->values[1]->value->value === 'y'
		];
		yield ['[x; y]', SetExpressionNode::class, fn(SetExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->values[1] instanceof VariableNameExpressionNode && $e->values[1]->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['[x;]', SetExpressionNode::class, fn(SetExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameIdentifier('x'))
		];
		yield ['[a: x, b: y]', RecordExpressionNode::class, fn(RecordExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values['a'] instanceof VariableNameExpressionNode && $e->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->values['b'] instanceof VariableNameExpressionNode && $e->values['b']->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["['a': x, 'b': y];", RecordExpressionNode::class, fn(RecordExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values['a'] instanceof VariableNameExpressionNode && $e->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->values['b'] instanceof VariableNameExpressionNode && $e->values['b']->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['[a: x]', RecordExpressionNode::class, fn(RecordExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values['a'] instanceof VariableNameExpressionNode && $e->values['a']->variableName->equals(new VariableNameIdentifier('x'))
		];
		yield ['=> x', ReturnExpressionNode::class, fn(ReturnExpressionNode $e) =>
			$e->returnedExpression instanceof VariableNameExpressionNode && $e->returnedExpression->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['?noError(x)', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof VariableNameExpressionNode && $e->targetExpression->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['?noExternalError(x)', NoExternalErrorExpressionNode::class, fn(NoExternalErrorExpressionNode $e) =>
			$e->targetExpression instanceof VariableNameExpressionNode && $e->targetExpression->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['x.y', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->propertyName === 'y'
		];
		yield ["x.'y';", PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->propertyName === 'y'
		];
		yield ['x.0', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->propertyName === 0
		];
		yield ['x.y.z', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof PropertyAccessExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->target->propertyName === 'y' &&
			$e->propertyName === 'z'
		];
		yield ['@x', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('Error')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameIdentifier('x'))];

		yield ['C', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) => $e->value instanceof AtomValueNode &&
			$e->value->name->equals(new TypeNameIdentifier('C'))];
		yield ['C()', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['C(x)', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['C[]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof TupleValueNode && count($e->parameter->value->values) === 0];
		yield ['C[x]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['C[x, y]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameIdentifier('y'))];
		yield ["C['x'];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x'];
		yield ["C['x', 'y'];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
						$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x' &&
						$e->parameter->values[1] instanceof ConstantExpressionNode && $e->parameter->values[1]->value instanceof StringValueNode && $e->parameter->values[1]->value->value === 'y'];

		yield ['C[;]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof SetValueNode && count($e->parameter->value->values) === 0];
		yield ['C[x;]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['C[x; y]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameIdentifier('y'))];

		yield ['C[:]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof RecordValueNode && count($e->parameter->value->values) === 0];
		yield ['C[a: x]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['C[a: x, b: y]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameIdentifier('y'))];
		yield ["C['a': x];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x'))];
		yield ["C['a': x, 'b': y];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameIdentifier('C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameIdentifier('y'))];

		yield ['f()', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['f(x)', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['f[]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof TupleValueNode && count($e->parameter->value->values) === 0];
		yield ['f[x]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['f[x, y]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameIdentifier('y'))];
		yield ["f['x'];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x'];
		yield ["f['x', 'y'];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
						$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x' &&
						$e->parameter->values[1] instanceof ConstantExpressionNode && $e->parameter->values[1]->value instanceof StringValueNode && $e->parameter->values[1]->value->value === 'y'];

		yield ['f[;]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof SetValueNode && count($e->parameter->value->values) === 0];
		yield ['f[x;]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['f[x; y]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameIdentifier('y'))];

		yield ['f[:]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof RecordValueNode && count($e->parameter->value->values) === 0];
		yield ['f[a: x]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['f[a: x, b: y]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameIdentifier('y'))];
		yield ["f['a': x];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x'))];
		yield ["f['a': x, 'b': y];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameIdentifier('y'))];

		yield ['a=>b', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof VariableNameExpressionNode && $e->targetExpression->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->targetExpression->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['a|>b', NoExternalErrorExpressionNode::class, fn(NoExternalErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof VariableNameExpressionNode && $e->targetExpression->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->targetExpression->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['+a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('unaryPlus')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['-a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('unaryMinus')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['~a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('unaryBitwiseNot')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['!a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('unaryNot')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];

		yield ['a + x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('binaryPlus')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['a - x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryMinus'))];
		yield ['a * x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryMultiply'))];
		yield ['a / x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryDivide'))];
		yield ['a // x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryIntegerDivide'))];
		yield ['a % x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryModulo'))];
		yield ['a ** x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryPower'))];
		yield ['a & x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryBitwiseAnd'))];
		yield ['a | x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryBitwiseOr'))];
		yield ['a ^ x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryBitwiseXor'))];
		yield ['a < x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryLessThan'))];
		yield ['a <= x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryLessThanEqual'))];
		yield ['a > x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryGreaterThan'))];
		yield ['a >= x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryGreaterThanEqual'))];
		yield ['a != x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryNotEqual'))];
		yield ['a == x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryEqual'))];
		yield ['a || x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryOr'))];
		yield ['a && x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryAnd'))];
		yield ['a ^^ x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameIdentifier('binaryXor'))];

		yield ['a *> (null)', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof VariableNameExpressionNode && $e->targetExpression->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->targetExpression->methodName->equals(new MethodNameIdentifier('errorAsExternal')) &&
			$e->targetExpression->parameter instanceof SequenceExpressionNode && count($e->targetExpression->parameter->expressions) === 1 &&
			$e->targetExpression->parameter->expressions[0] instanceof ConstantExpressionNode && $e->targetExpression->parameter->expressions[0]->value instanceof NullValueNode];

		yield ['a->b->c', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof MethodCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->target->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->target->parameter instanceof ConstantExpressionNode && $e->target->parameter->value instanceof NullValueNode &&
			$e->methodName->equals(new MethodNameIdentifier('c')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b(c)->d', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof MethodCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->target->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->target->parameter instanceof SequenceExpressionNode && count($e->target->parameter->expressions) === 1 &&
			$e->target->parameter->expressions[0] instanceof VariableNameExpressionNode && $e->target->parameter->expressions[0]->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->methodName->equals(new MethodNameIdentifier('d')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b.c', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof MethodCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->target->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->target->parameter instanceof ConstantExpressionNode && $e->target->parameter->value instanceof NullValueNode &&
			$e->propertyName === 'c'];

		yield ['a->b', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b(x)', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->variableName->equals(new VariableNameIdentifier('x'))];
		/*yield ['a->b[]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof ConstantExpressionNode &&
			$e->parameter->value instanceof TupleValueNode && count($e->parameter->value->values) === 0];*/
		yield ['a->b[x.y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof TupleExpressionNode && count($e->parameter->expressions[0]->values) === 1 &&
			$e->parameter->expressions[0]->values[0] instanceof PropertyAccessExpressionNode &&
			$e->parameter->expressions[0]->values[0]->target instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[0]->target->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->expressions[0]->values[0]->propertyName === 'y'
		];

		yield ['a->b[x]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof TupleExpressionNode && count($e->parameter->expressions[0]->values) === 1 &&
			$e->parameter->expressions[0]->values[0] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[0]->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['a->b[x, y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof TupleExpressionNode && count($e->parameter->expressions[0]->values) === 2 &&
			$e->parameter->expressions[0]->values[0] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->expressions[0]->values[1] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[1]->variableName->equals(new VariableNameIdentifier('y'))];
		yield ["a->b['x'];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof TupleExpressionNode && count($e->parameter->expressions[0]->values) === 1 &&
			$e->parameter->expressions[0]->values[0] instanceof ConstantExpressionNode && $e->parameter->expressions[0]->values[0]->value instanceof StringValueNode && $e->parameter->expressions[0]->values[0]->value->value === 'x'];
		yield ["a->b['x', 'y'];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof TupleExpressionNode && count($e->parameter->expressions[0]->values) === 2 &&
			$e->parameter->expressions[0]->values[0] instanceof ConstantExpressionNode && $e->parameter->expressions[0]->values[0]->value instanceof StringValueNode && $e->parameter->expressions[0]->values[0]->value->value === 'x' &&
			$e->parameter->expressions[0]->values[1] instanceof ConstantExpressionNode && $e->parameter->expressions[0]->values[1]->value instanceof StringValueNode && $e->parameter->expressions[0]->values[1]->value->value === 'y'];
		yield ['a->b[x;]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof SetExpressionNode && count($e->parameter->expressions[0]->values) === 1 &&
			$e->parameter->expressions[0]->values[0] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[0]->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['a->b[x; y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof SetExpressionNode && count($e->parameter->expressions[0]->values) === 2 &&
			$e->parameter->expressions[0]->values[0] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[0]->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->expressions[0]->values[1] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values[1]->variableName->equals(new VariableNameIdentifier('y'))];
		yield ['a->b[a: x]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof RecordExpressionNode && count($e->parameter->expressions[0]->values) === 1 &&
			$e->parameter->expressions[0]->values['a'] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values['a']->variableName->equals(new VariableNameIdentifier('x'))];
		yield ['a->b[a: x, b: y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof RecordExpressionNode && count($e->parameter->expressions[0]->values) === 2 &&
			$e->parameter->expressions[0]->values['a'] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->expressions[0]->values['b'] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values['b']->variableName->equals(new VariableNameIdentifier('y'))];
		yield ["a->b['a': x];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof RecordExpressionNode && count($e->parameter->expressions[0]->values) === 1 &&
			$e->parameter->expressions[0]->values['a'] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values['a']->variableName->equals(new VariableNameIdentifier('x'))];
		yield ["a->b['a': x, 'b': y];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->methodName->equals(new MethodNameIdentifier('b')) &&
			$e->parameter instanceof SequenceExpressionNode && count($e->parameter->expressions) === 1 &&
			$e->parameter->expressions[0] instanceof RecordExpressionNode && count($e->parameter->expressions[0]->values) === 2 &&
			$e->parameter->expressions[0]->values['a'] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values['a']->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->parameter->expressions[0]->values['b'] instanceof VariableNameExpressionNode && $e->parameter->expressions[0]->values['b']->variableName->equals(new VariableNameIdentifier('y'))];
		//more
		yield ['?whenIsError(x) { y }', MatchErrorExpressionNode::class, fn(MatchErrorExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->onError instanceof SequenceExpressionNode && count($e->onError->expressions) === 1 &&
			$e->onError->expressions[0] instanceof VariableNameExpressionNode && $e->onError->expressions[0]->variableName->equals(new VariableNameIdentifier('y')) &&
			$e->else === null
		];
		yield ['?whenIsError(x) { y } ~ { z }', MatchErrorExpressionNode::class, fn(MatchErrorExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->onError instanceof SequenceExpressionNode && count($e->onError->expressions) === 1 &&
			$e->onError->expressions[0] instanceof VariableNameExpressionNode && $e->onError->expressions[0]->variableName->equals(new VariableNameIdentifier('y')) &&
			$e->else instanceof SequenceExpressionNode && count($e->else->expressions) === 1 &&
			$e->else->expressions[0] instanceof VariableNameExpressionNode && $e->else->expressions[0]->variableName->equals(new VariableNameIdentifier('z'))
		];
		yield ['?when(x) { y }', MatchIfExpressionNode::class, fn(MatchIfExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->then instanceof SequenceExpressionNode && count($e->then->expressions) === 1 &&
			$e->then->expressions[0] instanceof VariableNameExpressionNode && $e->then->expressions[0]->variableName->equals(new VariableNameIdentifier('y')) &&
			$e->else instanceof ConstantExpressionNode && $e->else->value instanceof NullValueNode
		];
		yield ['?when(x) { y } ~ { z }', MatchIfExpressionNode::class, fn(MatchIfExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameIdentifier('x')) &&
			$e->then instanceof SequenceExpressionNode && count($e->then->expressions) === 1 &&
			$e->then->expressions[0] instanceof VariableNameExpressionNode && $e->then->expressions[0]->variableName->equals(new VariableNameIdentifier('y')) &&
			$e->else instanceof SequenceExpressionNode && count($e->else->expressions) === 1 &&
			$e->else->expressions[0] instanceof VariableNameExpressionNode && $e->else->expressions[0]->variableName->equals(new VariableNameIdentifier('z'))
		];
		yield ['?whenTypeOf(x) is { a: b }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b'))
		];
		yield ['?whenTypeOf(x) is { a: b, c: d }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameIdentifier('d'))
		];
		yield ['?whenTypeOf(x) is { ~: e }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('e'))
		];
		yield ['?whenTypeOf(x) is { a: b, c: d, ~: e }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 3 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode && $e->pairs[2] instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameIdentifier('d')) &&
			$e->pairs[2]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[2]->valueExpression->variableName->equals(new VariableNameIdentifier('e'))
		];
		yield ['?whenValueOf(x) is { a: b }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b'))
		];
		yield ['?whenValueOf(x) is { a: b, c: d }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameIdentifier('d'))
		];
		yield ['?whenValueOf(x) is { ~: e }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('e'))
		];
		yield ['?whenValueOf(x) is { a: b, c: d, ~: e }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameIdentifier('x')) &&
			count($e->pairs) === 3 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode && $e->pairs[2] instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameIdentifier('d')) &&
			$e->pairs[2]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[2]->valueExpression->variableName->equals(new VariableNameIdentifier('e'))
		];
		yield ['?whenIsTrue { a: b }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b'))
		];
		yield ['?whenIsTrue { a: b, c: d }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameIdentifier('d'))
		];
		yield ['?whenIsTrue { ~: e }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('e'))
		];
		yield ['?whenIsTrue { a: b, c: d, ~: e }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 3 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode && $e->pairs[2] instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameIdentifier('a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameIdentifier('b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameIdentifier('c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameIdentifier('d')) &&
			$e->pairs[2]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[2]->valueExpression->variableName->equals(new VariableNameIdentifier('e'))
		];

		yield ['var{a} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames[0]->equals(new VariableNameIdentifier('a')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{a, b} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames[0]->equals(new VariableNameIdentifier('a')) &&
			$e->variableNames[1]->equals(new VariableNameIdentifier('b')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{a, b, c} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 3 &&
			$e->variableNames[0]->equals(new VariableNameIdentifier('a')) &&
			$e->variableNames[1]->equals(new VariableNameIdentifier('b')) &&
			$e->variableNames[2]->equals(new VariableNameIdentifier('c')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{~a} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('a')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{a: x} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{'a': x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{is: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['is']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{true: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['true']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{false: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['false']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{null: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['null']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{var: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['var']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{type: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['type']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{mutable: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['mutable']->equals(new VariableNameIdentifier('x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{~a, ~b} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('a')) &&
			$e->variableNames['b']->equals(new VariableNameIdentifier('b')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{a: x, ~b} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('x')) &&
			$e->variableNames['b']->equals(new VariableNameIdentifier('b')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{~a, b: z} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('a')) &&
			$e->variableNames['b']->equals(new VariableNameIdentifier('z')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{a: x, b: z} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('x')) &&
			$e->variableNames['b']->equals(new VariableNameIdentifier('z')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{'a': x, 'b': z} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('x')) &&
			$e->variableNames['b']->equals(new VariableNameIdentifier('z')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ["var{'a': x, is: z, true: i, false: j, null: k, type: l, var: m, mutable: n} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 8 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('x')) &&
			$e->variableNames['is']->equals(new VariableNameIdentifier('z')) &&
			$e->variableNames['true']->equals(new VariableNameIdentifier('i')) &&
			$e->variableNames['false']->equals(new VariableNameIdentifier('j')) &&
			$e->variableNames['null']->equals(new VariableNameIdentifier('k')) &&
			$e->variableNames['type']->equals(new VariableNameIdentifier('l')) &&
			$e->variableNames['var']->equals(new VariableNameIdentifier('m')) &&
			$e->variableNames['mutable']->equals(new VariableNameIdentifier('n')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
		yield ['var{~a, ~b, ~c} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 3 &&
			$e->variableNames['a']->equals(new VariableNameIdentifier('a')) &&
			$e->variableNames['b']->equals(new VariableNameIdentifier('b')) &&
			$e->variableNames['c']->equals(new VariableNameIdentifier('c')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameIdentifier('y'))
		];
	}

	#[DataProvider('types')]
	public function testParseTypes(string $code, string $className, callable|null $checker = null): void {
		//$code .= ';';
		[$s] = $this->runParserTest($code, 701);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
		//echo $this->transitionLogger;
		$this->checkPosition($code, $s->generated);
	}

	public static function types(): iterable {
		yield ['Nothing', NothingTypeNode::class];
		yield ['\\Nothing', NothingTypeNode::class];
		yield ['True', TrueTypeNode::class];
		yield ['\\True', TrueTypeNode::class];
		yield ['False', FalseTypeNode::class];
		yield ['\\False', FalseTypeNode::class];
		yield ['Boolean', BooleanTypeNode::class];
		yield ['\\Boolean', BooleanTypeNode::class];
		yield ['Any', AnyTypeNode::class];
		yield ['\\Any', AnyTypeNode::class];
		yield ['Integer', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['\\Integer', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['Integer<5..>', IntegerTypeNode::class, fn($t) => $t->minValue == new Number('5') && $t->maxValue === PlusInfinity::value];
		yield ['Integer<..-8>', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8')];
		yield ['Integer<-5..8>', IntegerTypeNode::class, fn($t) => $t->minValue == new Number('-5') && $t->maxValue == new Number('8')];
		yield ['Integer[5]', IntegerSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0] == new Number('5')];
		yield ['Integer[5, -8]', IntegerSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0] == new Number('5') && $t->values[1] == new Number('-8')];
		yield ['Integer<[5..8], (..6)>', IntegerFullTypeNode::class, fn($t) => count($t->intervals) === 2 && $t->intervals[0]->start == new NumberIntervalEndpoint(new Number(5), true) && $t->intervals[0]->end == new NumberIntervalEndpoint(new Number(8), true) && $t->intervals[1]->start === MinusInfinity::value && $t->intervals[1]->end == new NumberIntervalEndpoint(new Number(6), false)];
		yield ['Real', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['\\Real', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['Real<5..>', RealTypeNode::class, fn($t) => $t->minValue == new Number('5') && $t->maxValue === PlusInfinity::value];
		yield ['Real<..-8>', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8')];
		yield ['Real<-5..8>', RealTypeNode::class, fn($t) => $t->minValue == new Number('-5') && $t->maxValue == new Number('8')];
		yield ['Real<5.14..>', RealTypeNode::class, fn($t) => $t->minValue == new Number('5.14') && $t->maxValue === PlusInfinity::value];
		yield ['Real<..-8.14>', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8.14')];
		yield ['Real<-5.14..8.14>', RealTypeNode::class, fn($t) => $t->minValue == new Number('-5.14') && $t->maxValue == new Number('8.14')];
		yield ['Real[3.14]', RealSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0] == new Number('3.14')];
		yield ['Real[3.14, -8]', RealSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0] == new Number('3.14') && $t->values[1] == new Number('-8')];
		yield ['Real<[5.15..8], (..6)>', RealFullTypeNode::class, fn($t) => count($t->intervals) === 2 && $t->intervals[0]->start == new NumberIntervalEndpoint(new Number('5.15'), true) && $t->intervals[0]->end == new NumberIntervalEndpoint(new Number(8), true) && $t->intervals[1]->start === MinusInfinity::value && $t->intervals[1]->end == new NumberIntervalEndpoint(new Number(6), false)];
		yield ['String', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\String', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['String<5..>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['String<..8>', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['String<5>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['String<5..8>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ["String['hello']", StringSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0] == 'hello'];
		yield ["String['hello', 'world']", StringSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0] == 'hello' && $t->values[1] == 'world'];
		yield ['Array', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Array', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Array<5..>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Array<..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Array<5..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Array<Boolean>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Array<Boolean, 5..>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Array<Boolean, ..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Array<Boolean, 5..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Map', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Map', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Map<5..>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Map<..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Map<5..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Map<Boolean>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Map<Boolean, 5..>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Map<Boolean, ..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Map<Boolean, 5..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Set', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Set', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Set<5..>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Set<..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Set<5..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Set<Boolean>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Set<Boolean, 5..>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Set<Boolean, ..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Set<Boolean, 5..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];

		yield ['MyCustomType', NamedTypeNode::class, fn($t) => $t->name->equals(new TypeNameIdentifier('MyCustomType'))];
		yield ['\\MyProxyType', ProxyTypeNode::class, fn($t) => $t->name->equals(new TypeNameIdentifier('MyProxyType'))];
		yield ['Type', TypeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['\\Type', TypeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['Type<Boolean>', TypeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['Type<Tuple>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::Tuple];
		yield ['Type<\\EnumerationValue>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::EnumerationValue];
		yield ['Type<\\MutableValue>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::MutableValue];
		yield ['Type<Integer|Boolean>', TypeTypeNode::class, fn($t) => $t->refType instanceOf UnionTypeNode &&
			$t->refType->left instanceOf IntegerTypeNode && $t->refType->right instanceOf BooleanTypeNode];
		yield ['Mutable', MutableTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['\\Mutable', MutableTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['Mutable<Boolean>', MutableTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['Shape', ShapeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['\\Shape', ShapeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['Shape<Boolean>', ShapeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['{Boolean}', ShapeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['Result', ResultTypeNode::class, fn($t) => $t->returnType instanceOf AnyTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['\\Result', ResultTypeNode::class, fn($t) => $t->returnType instanceOf AnyTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Result<Null>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NullTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Result<Null, Boolean>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NullTypeNode && $t->errorType instanceOf BooleanTypeNode];
		yield ['Error', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['\\Error', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Error<Boolean>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf BooleanTypeNode];
		yield ['Impure', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['\\Impure', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['Impure<Boolean>', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['*Boolean', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['[]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf NothingTypeNode];
		yield ['[...]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf AnyTypeNode];
		yield ['[... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf BooleanTypeNode];
		yield ['[[Null]]', TupleTypeNode::class, fn($t) => count($t->types) === 1 &&
			$t->types[0] instanceof TupleTypeNode && count($t->types[0]->types) === 1 && $t->restType instanceOf NothingTypeNode &&
			$t->types[0]->types[0] instanceof NullTypeNode && $t->types[0]->restType instanceOf NothingTypeNode];
		yield ['[Null]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[Null, ...]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[Null, ... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[Null, Any]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[Null, Any, ...]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[Null, Any, ... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[:]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf NothingTypeNode];
		yield ['[: ...]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf AnyTypeNode];
		yield ['[: ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf BooleanTypeNode];
		yield ['[~A]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NamedTypeNode && $t->types['a']->name->equals(new TypeNameIdentifier('A')) && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null, ...]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[a: Null, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null, b: Any]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null, b: Any, ...]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[a: Null, b: Any, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null, b: Any, c: ?Null, d: OptionalKey<Null>, e: OptionalKey, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 5 &&
			$t->types['a'] instanceof NullTypeNode &&
			$t->types['b'] instanceof AnyTypeNode &&
			$t->types['c'] instanceof OptionalKeyTypeNode && $t->types['c']->valueType instanceof NullTypeNode &&
			$t->types['d'] instanceof OptionalKeyTypeNode && $t->types['d']->valueType instanceof NullTypeNode &&
			$t->types['e'] instanceof OptionalKeyTypeNode && $t->types['e']->valueType instanceof AnyTypeNode &&
			$t->restType instanceOf BooleanTypeNode];
		yield ["['a': Null, 'b': Any, ... Boolean]", RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];

		yield ['MyEnum[Value1]', EnumerationSubsetTypeNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyEnum')) && count($v->values) === 1 &&
			$v->values[0]->equals(new EnumValueIdentifier('Value1'))];
		yield ['MyEnum[Value1, Value2]', EnumerationSubsetTypeNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyEnum')) && count($v->values) === 2 &&
			$v->values[0]->equals(new EnumValueIdentifier('Value1')) && $v->values[1]->equals(new EnumValueIdentifier('Value2'))];
		yield ['^Null => Any', FunctionTypeNode::class, fn($v) => $v->parameterType instanceOf NullTypeNode && $v->returnType instanceOf AnyTypeNode];
		yield ['^Null', FunctionTypeNode::class, fn($v) => $v->parameterType instanceOf NullTypeNode && $v->returnType instanceOf AnyTypeNode];

		yield ['Boolean|Null', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['(Boolean|Null)', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['Boolean|Null|Type', UnionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['(Boolean|Null|Type)', UnionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		//yield ['Any|Nothing|Boolean|True|False|Null|MutableValue|EnumerationValue', UnionTypeNode::class];
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
		yield ['(Boolean|Null)&Type', IntersectionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode && $t->right instanceof TypeTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode];
		yield ['(Boolean&Null)|Type', UnionTypeNode::class, fn($t) => $t->left instanceof IntersectionTypeNode && $t->right instanceof TypeTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode];

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
		$this->checkPosition($code, $s->generated);
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
		yield ['MyAtom', AtomValueNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyAtom'))];
		yield ['MyEnum.Value', EnumerationValueNode::class, fn($v) => $v->name->equals(new TypeNameIdentifier('MyEnum')) && $v->enumValue->equals(new EnumValueIdentifier('Value'))];
		yield ['^ :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^ %% True :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof TrueTypeNode];
		yield ['^ => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^p :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^p %% True :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof TrueTypeNode];
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
		yield ['^Null %% True :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof TrueTypeNode];
		yield ['^p: False => Any :: null', FunctionValueNode::class, fn($v) => $v->parameterName->equals(new VariableNameIdentifier('p')) && $v->parameterType instanceof FalseTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
		yield ['^[Any] :: null', FunctionValueNode::class, fn($v) => $v->parameterName === null && $v->parameterType instanceof TupleTypeNode &&
									count($v->parameterType->types) === 1 && $v->parameterType->types[0] instanceof AnyTypeNode && $v->returnType instanceof AnyTypeNode && $v->dependencyType instanceof NothingTypeNode];
	}

}