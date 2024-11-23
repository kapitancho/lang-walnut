# Introduction
Walnut is a programming language that is designed to be easy to use for modelling and executing business logic. 
It is a strongly typed interpreted language with many functional concepts taken into consideration.
It is a good choice for domain driven design and hexagonal architecture. 

### What is a Walnut program?
In a nutshell, a Walnut program is a collection of type and function/method definitions optionally split into modules.
```walnut
module product:

Product = [id: Integer<1..>, name: String<1..100>, price: Real<0..>];
getProductName = ^Product => String<1..100> :: #.name;
```

### Types and values
Walnut has one of the most expressive type systems among the programming languages. Relying on set theory and subtyping,
it offers a wide range of types that can be used to model the domain of the problem.
The values could be numbers (integers and reals), strings, booleans, arrays, maps, functions, types, atoms, and more.
Even the built-in types can be subtyped.
```walnut
OddInteger <: Integer @ NotAnOddInteger :: ?whenValueOf(# % 2) is { 0: Error(NotAnOddInteger[]) };
x = OddInteger[5];
x + 1; /* = 6 */
```
The functions and the types are first-class citizens and can be passed between functions.
A detailed description of the types and values can be found [here](02-types-and-values.md).

### Immutability
The values in Walnut are immutable. The only way to specify a mutable value is by using the special `Mutable<T>` type.

### Encapsulation
Why functional in its core the language supports encapsulation. This is done by defining sealed types.
```walnut
Product = $[id: Integer<1..>, name: String<1..100>, price: Real<0..>];
p = Product[1, 'Apple', 1.5];
p.name /* Cannot access the properties from outside */

Product->name(^Null => String<1..100) :: $name; /* Works ok */
Product ==> String :: $name->concatList[': ', $price->asString]; /* Cast to String */
```

### Functions
A basic function is a 4-tuple consisting of a parameter type, a return type, a dependency type and a body in the form of an expression. 
```walnut
getLength = ^String<1..10> => Integer<1..10> %% [~SomeDependency] :: #->length;
```
In addition to the basic functions, Walnut supports behavior-driven functions (or methods) which are assigned to types.
They are a 6-tuple consisting of a target type, a method name, a dependency type, a parameter type, a return type, 
and a body in the form of an expression.
```walnut
Article->publish(^Null => ArticlePublished) %% [~Clock] :: {
    $.publishDate->SET(%.clock->now);
    ArticlePublished[]
};
```
The dependency type is related to the integrated dependency injection mechanism, and it is optional.
There are no built-in functions in Walnut but the language provides many powerful methods assigned to the 
built-in types. While being an OOP-like syntactic sugar, in their core they are just functions.

### Expressions
Everything in Walnut is an expression. They are grouped into the following categories: values, variable assignments, 
early returns, conditionals, function calls, and sequences.
```walnut
myFn = ^Integer => [Integer, Integer] :: {
    x = ?whenValueOf(#) is { 42: 1, 1000: 2, ~: 0 };
    [x, x + 1]
};
```

### Modules
The Walnut programs can be organized into modules. A module is a collection of type and function definitions.
A module may import any number of other modules. The core module is imported implicitly and there are several
built-in modules that can be imported as well.
```walnut
module product %% db, datetime, http-core:
/* some code */
```

### Entry Points
The language can be used in both CLI and HTTP environments. In the CLI environment, the entry point is the 
`main` function. In the HTTP environment, the entry point is the `handleRequest` function.

```walnut
module hello-world:
main = ^Array<String> => String :: 'Hello, World!';
```
Additionally, there is an interface allowing calls to any global function. 

### Runtime
The language specification is not bound to a specific runtime. In reality though as of 2024 the language runs
using PHP. At some point in the future, it might get a different runtime but this is not yet planned.