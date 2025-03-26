# Types and values

Walnut has one of the most expressive type systems among the programming languages. Relying on set theory and subtyping,
it offers a wide range of types that can be used to model the domain of the problem.

## Numbers

The following types and values are available for numbers:

### Types
- `Integer` / `Integer<1..10>` / `Integer<0..>` / `Integer<..100>` - integers and ranged integers
- `Integer[5]`, `Integer[1, 3, 20]` - integer number subsets
- `Real` / `Real<-3.14..3.14>` / `Real<0..>` / `Real<..99.9>` - real numbers and ranged reals
- `Real[3.14]`, `Real[1.5, 3.14, 20]` - real number subsets

### Values
- `3`, `5`, `-7` - integers (`3->type` is `Integer[3]`)
- `3.14`, `0.1`, `-2.61`, `7.0` - real numbers (`3.14->type` is `Real[3.14]`)


## Strings

The following types and values are available for strings:

### Types
- `String` / `String<1..10>` / `String<5..>` / `String<..100>` - strings and strings with a length range
- `String['Hello']`, `String['Hello', 'World']` - string subsets

### Values
- `'Hello'`, `'World'`, `'Hello, World!'`, ``'I\`m a teapot with a backslash \\ and a newline \n'`` - strings (`'Hello'->type` is `String['Hello']`)


## Atoms and Null

Atoms are types with a single value and `Null` is a special `Atom` type.

### Types
- `Null` - built-in Atom type
- `MyAtom = :[]` - Atom type definition

### Values
- `null` - the value of the Null type (`null->type` is `Null`)
- `MyAtom()` - the value of the `MyAtom` type (`MyAtom()->type` is `MyAtom`)

## Enumerations and Boolean

Enumerations are types with a fixed set of values. The `Boolean` type is a special case of an enumeration.

### Types
- `Suit = :[Clubs, Diamonds, Hearts, Spades]` - enumeration type definition
- `BlackSuit = Suit[Clubs, Spades]` - enumeration subset type based on the `Suit` enumeration type
- `Boolean` - built-in enumeration type
- `True`, `False` - enumeration subset types based on `Boolean`

### Values
- `Suit.Spades` - enumeration value (`Suit.Spade->type` is `Suit[Spades]`)
- `true`, `false` - boolean values (`true->type` is `True` and `false->type` is `False`)

## Arrays, Tuples, Maps, Records, and Sets

The following types and values are available for arrays, tuples, maps, records, and sets:

### Types
- `Array`, `Array<Integer>`, `Array<String, 1..5>`, `Array<..10>`, `Array<Array, 3..>>` - arrays and arrays with length range and/or element type constraints 
- `Map`, `Map<Integer>`, `Map<String, 1..5>`, `Map<..10>`, `Map<Map, 3..>>` - maps and maps with length range and/or value type constraints
- `Set`, `Set<Integer>`, `Set<String, 1..5>`, `Set<..10>`, `Set<Map, 3..>>` - sets and sets with length range and/or value type constraints
- `[]`, `[Real]`, `[Integer, String]`, `[Any, Array, Integer<1..5>, ...]`, `[Integer, ...Real]` - tuples and tuples with rest type definition 
- `[:]`, `[a: Real]`, `[x: Integer, y: String]`, `[a: Any, b: ?String]`, `[a: Integer, ... Real]` - records and records with optional keys and rest type definition

### Values
- `[]`, `[1]`, `[1, 'Hello', null]` - tuples (`[1, 'Hello', null]->type` is `[Integer[1], String['Hello'], Null]`)
- `[:]`, `[a: 1]`, `[x: 1, y: 'Hello', z: null]` - records (`[x: 1, y: 'Hello', z: null]->type` is `[x: Integer[1], y: String['Hello'], z: Null]`)
- `[;]`, `[1;]`, `[1; 'Hello'; null]` - sets (`[1; 'Hello'; null]->type` is `Set<Integer[1]|String['Hello']|Null, 3..3>`)

### Subtyping between types
- `[Integer, Real]` is a subtype of `Array<Real, ..5>`
- `[a: Integer, b: Real, z: Boolean]` is a subtype of `Map<Real, 2..>` and a subtype of `[z: Boolean, ... Real]`

`?T` is a shorthand for `OptionalKey<T>`. This special type can only be used in record type definitions. 

## Mutable

The values in Walnut are immutable. The only way to specify a mutable value is by using the special `Mutable<T>` type.

### Types
- `Mutable`, `Mutable<Integer<0..100>>`, `Mutable<[a: String, b: Integer]>` - mutable types

### Values
- `Mutable[type{Integer<0..100>}, 5], mutable{Real, 3.14}, mutable{Any, 'Hello'}` - mutable values (`mutable{Real, 3.14}->type` is `Mutable[Real]`)

## Result, Error, and Impure

Every value in Walnut can be wrapped as an error. The errors have special effect in some methods like `Array->map` and in some control structures like `?noError`.
More about errors can be read [here](23-working-with-error-values.md)


### Types
- `Result<Integer, String>` - the value is either a string error or an integer. Informally this corresponds to `Integer|Error<String>`.
- `Error`, `Error<String>` - error types. `Error<T>` is a shorthand of `Result<Nothing, T>`.
- `Error<ExternalError>` - the special external error type

### Values
- `@'File not found'`, `@Error('File not found')`, `@UnknownProduct[15]` - error values (`@'File not found'->type` is `Error<String>`/`Result<Nothing, String>`)
- `@ExternalError[...]` - external error values (`@ExternalError[...]->type` is `Error<ExternalError>`)

### Shorthands for types containing `ExternalError`
- `*Integer` = `Impure<Integer>` = `Result<Integer, ExternalError>`
- `*Result<Integer, String>` = `Impure<Result<Integer, String>>` = `Result<Integer, ExternalError|String>`

## Sealed

Sealed types are used to encapsulate the data and behavior similarly to OOP. They must be defined based on a record type.

### Types
- `Product = $[id: Integer<1..>, name: String<1..100>, price: Real<0..>]` - sealed type definition

### Values
- `p = Product[1, 'Apple', 1.5]` - sealed type value (`p->type` is `Product`)

Check [Functions](03-functions.md#Constructors) for more information on how to define a constructor and an invariant checker.

## Open

Open types are similar to structs and records in other languages. Their data is accessible.

### Types
- `OddInteger = #Integer @ NotAnOddInteger :: ?whenValueOf(# % 2) is { 0: Error(NotAnOddInteger[]) }` - open type definition
- `GpsPoint = [latitude: Real<-90..90>, longitude: Real<-180..180>]` - open type definition based on a tuple type

### Values
- `x = OddInteger(5)` - open type value (`x->type` is `OddInteger`, `x->value` is `5`)
- `p = GpsPoint[51.5074, 0.1278]` - open type value (`p->type` is `GpsPoint`, `p.0` is `51.5074`)

## Function

The functions are first-class citizens in Walnut. They can be passed as arguments, returned from other functions, and assigned to variables.

### Types
- `^Integer => Real`, `^[x: Integer, y: Real] => Result<Boolean, String>` - function type definition

### Values
- `f = ^i: Integer => Real %% [~MyDependency] :: i * 3.14` - function value (`f->type` is `^Integer => Real`)

## Type

The types can be used as values in Walnut. They can be passed as arguments, returned from other functions, and assigned to variables.

### Types
- `Type<Integer>`, `Type<Array<Integer, ..5>>`, `Type<[a: Integer, b: String]>`, `type<[Integer, Real]>`, `Type<Sealed>` - type types

### Values
- `type{Integer}`, `type{Array<Integer, ..5>}`, `type[a: Integer, b: String]`, `type[Integer, Real]`, `type[Sealed]` - type values

The following special meta types are only available in type definitions (`Type<T>`):
`Function`, `Tuple`, `Record`, `Union`, `Intersection`, `Atom`, `Enumeration`, `EnumerationSubset`, `IntegerSubset`, `RealSubset`, `StringSubset`, `Alias`, `Subtype`, `Sealed`, `Named`

## Other types

### Aliases
They provide a way to define a new name for an existing type. They are useful for simplifying complex type definitions.
In addition, methods can be assigned to an alias type.
- `MyInt = Integer<1..10>` - alias type definition
- `MyInt->doubled(^Null => Integer<2..20) :: # * 2` - method assignment to an alias type
- `x->doubled` - method call on a value of the alias type `MyInt` provided that the type is known on compile-time.

### Shapes
The shape types define a common way to access different values which share a similar "shape".
- `Shape<Integer>` (or in short syntax - `{Integer}`)
- `Shape<[x: Real, y: Real, ...]>` (or in short syntax - `{x: Real, y: Real, ...}`) 

A value `v` is of type `Shape<T>` if its type `V` is a subtype of `T` or `V` is an open type based on `T` or 
there is a defined cast method `V ==> T` with no error return type.
```walnut
getName = ^obj: {String} => String :: 'The name is: + obj->shape(`{String});

getName(Product[id: 1, title: 'Tomato', price: 2.34]); //returns 'The name is: Tomato'
```

### Any
This is the top type in Walnut. It can be used to represent any value. Type like `Array` and `Map` are in fact `Array<Any>` and `Map<Any>`.

### Nothing
This is the bottom type in Walnut. It can be used to represent no value. `Error<T>` is in fact `Result<Nothing, T>`, 
`[Integer]` is `[Integer, ...Nothing]` and `[a: String]` is `[a: String, ...Nothing]`

### Union and Intersection
These are used to combine types. The union type `A|B` is a type that is either `A` or `B`. The intersection type `A&B` is a type that is both `A` and `B`.
- `Integer|Real`, `Array<Integer>|Array<Real>`, `Array<Integer>&Array<Real>`, `Array<Integer>&Array<Real>&Array<String>` - union and intersection types.

### Proxy 
There is no real Proxy type but in some rare cases a type should be referenced before its definition. The only way to do this is by using a Proxy type.
- ``NodeElement = [left: `Node, value: Integer, right: `Node]; Node = NodeElement|Null;`` - proxy type usage