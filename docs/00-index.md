# Walnut Language Specification

**Version:** 1.0
**Last Updated:** October 2025

## Overview

Walnut is a strongly typed, functional programming language designed for modeling and executing business logic with precision and clarity. It features one of the most expressive type systems among programming languages, built on set-theoretical foundations with comprehensive subtyping support.

## Key Features

- **Expressive Type System**: Set-theory based types with union, intersection, and refinement types
- **Functional Core**: Immutability by default with first-class functions
- **Dependency Injection**: Built-in compile-time dependency resolution
- **Error Handling**: Result types with sophisticated error propagation
- **Method-Oriented**: Behavior-driven functions attached to types
- **Compile-Time Safety**: Strong static type checking with type inference

## Language Specification Sections

### Part I: Fundamentals

1. [**Lexical Structure**](01-lexical-structure.md)
   - Tokens, identifiers, keywords, literals
   - Comments and whitespace
   - Operators and punctuation

2. [**Type System**](02-type-system.md)
   - Type hierarchy and subtyping
   - Primitive types (Integer, Real, String, Boolean)
   - Composite types (Array, Map, Set, Tuple, Record)
   - Advanced types (Union, Intersection, Shape)

3. [**Values and Literals**](03-values-literals.md)
   - Literal syntax for all types
   - Constant expressions
   - Type inference from values

### Part II: Type Definitions

4. [**User-Defined Types**](04-user-defined-types.md)
   - Atoms and Enumerations
   - Data types
   - Open types
   - Sealed types
   - Type aliases

5. [**Type Refinement**](05-type-refinement.md)
   - Range constraints
   - Length constraints
   - Value subsets
   - Enumeration subsets

6. [**Integers and Reals**](06-integers-reals.md)
   - Integer and Real types
   - Arbitrary precision arithmetic
   - Type refinements and constraints
   - Arithmetic, comparison, and bitwise operations
   - Math functions
   - Casting to and from Integer and Real

7. [**Strings**](07-strings.md)
   - String type and UTF-8 encoding
   - String literals and escape sequences
   - Type refinements (length, value subsets)
   - String methods (case conversion, trimming, searching, extraction)
   - String modification and JSON operations
   - Casting to and from String

8. [**Booleans**](08-booleans.md)
   - Boolean type and True/False subtypes
   - Logical operations (AND, OR, XOR, NOT)
   - Comparison operations
   - Pattern matching with booleans
   - Casting to and from Boolean

9. [**Arrays and Tuples**](09-arrays-tuples.md)
   - Array and Tuple types
   - Type refinements (length, element types)
   - Array operations (searching, filtering, mapping, sorting)
   - Tuple operations and decomposition
   - Mutable array operations
   - Casting and conversions

10. [**Maps and Records**](10-maps-records.md)
    - Map and Record types
    - Type refinements (size, value types, optional fields)
    - Map operations (modification, transformation, aggregation)
    - Record field access and operations
    - Casting and conversions
    - JSON serialization

11. [**Union and Intersection Types**](11-union-intersection.md)
    - Union types (alternatives)
    - Intersection types (combinations)
    - Type narrowing and pattern matching
    - Type reflection with unions and intersections
    - Practical examples and best practices

12. [**Mutable Values**](12-mutable-values.md)
    - Mutable type syntax
    - Mutation operations
    - Mutable semantics

13. [**Reflection and Metaprogramming**](13-reflection-metaprogramming.md)
    - Type values (`Type<T>`)
    - Meta-types
    - Type inspection
    - Dynamic type operations

### Part III: Functions, Methods, and Control Flow

14. [**Functions**](14-functions.md)
    - Function syntax and types
    - Parameters and return types
    - Function variables and captures
    - Dependency parameters

15. [**Methods**](15-methods.md)
    - Method definitions
    - Target types
    - Variable access ($, #, %, $$, $_)
    - Method resolution

16. [**Constructors and Casts**](16-constructors-casts.md)
    - Constructor definitions
    - Invariant validators
    - Cast functions
    - Type conversions

17. [**Expressions**](17-expressions.md)
    - Expression categories
    - Sequence expressions
    - Variable assignments
    - Scoped expressions

18. [**Conditional Expressions**](18-conditional-expressions.md)
    - `?when` - if-then-else
    - `?whenValueOf` - value matching
    - `?whenTypeOf` - type matching
    - `?whenIsTrue` - boolean conditions
    - `?whenIsError` - error handling

19. [**Early Returns and Error Handling**](19-early-returns-errors.md)
    - Unconditional return (`=>`)
    - Error returns (`?noError`, `?noExternalError`)
    - Shorthand operators (`=>`, `|>`, `*>`)
    - Result types

### Part IV: Built-in Library

20. [**Core Methods**](20-core-methods.md)
    - Any methods
    - Numeric methods (Integer, Real)
    - String methods
    - Collection methods (Array, Map, Set, Tuple, Record)

21. [**Standard Library Types**](21-standard-library.md)
    - Clock
    - Random
    - File
    - JsonValue
    - HttpRequest/HttpResponse

### Part V: Advanced Features and Practical Usage

22. [**Hydration**](22-hydration.md)
    - Converting runtime values to typed values
    - Hydration by type category
    - JsonValue casts
    - Error handling

23. [**Module System**](23-module-system.md)
    - Module declarations
    - Module dependencies
    - Package configuration
    - Import resolution

24. [**Dependency Injection**](24-dependency-injection.md)
    - Dependency declaration
    - Dependency resolution
    - DependencyContainer

25. [**Entry Points**](25-entry-points.md)
    - CLI entry points (`>>>`)
    - HTTP entry points
    - Function invocation interface

26. [**Testing**](26-testing.md)
    - Test file format (`.test.nut`)
    - Test cases
    - Assertions

27. [**Templates**](27-templates.md)
    - Template syntax (`.nut.html`)
    - Expression embedding
    - Template usage

## Quick Reference

### Syntax at a Glance

```walnut
/* Module declaration */
module myapp:

/* Type definitions */
ProductId := Integer<1..>;
Product := $[id: ProductId, name: String<1..100>, price: Real<0..>];

/* Methods */
Product->name(=> String) :: $name;
Product ==> String :: $name + ': $' + $price->asString;

/* Functions */
getDiscount = ^price: Real<0..> => Real<0..> :: ?when(price > 100) {
    price * 0.1
} ~ { 0 };

/* Entry point */
>>> {
    p = Product[1, 'Apple', 1.50];
    p->as(`String)
};
```

## Conventions Used in This Specification

- **Syntax**: Shown in `monospace` font
- **Meta-variables**: Shown in *italics*
- **Optional elements**: Enclosed in square brackets [like this]
- **Alternatives**: Separated by | (vertical bar)
- **Examples**: Provided in code blocks with syntax highlighting

## Additional Resources

- [GitHub Repository](https://github.com/kapitancho/walnut-lang)
- [Tutorial](../01-introduction.md)
- [Example Programs](../../walnut-src/)
