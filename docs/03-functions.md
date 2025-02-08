# Functions

## Basic functions
A basic function is a 4-tuple consisting of a parameter type, a return type, a dependency type and a body in the form of an expression. 

#### Example
```walnut
getLength = ^s: String<1..10> => Integer<1..10> %% [~SomeDependency] :: s->length;
```
The parameter value is always accessible using the `#` variable but in case a variable name is provided
the value is available using this variable as well. In the example above this is `s`. 
Since the functions accept only one parameter, the way to pass more than one value is to use Tuples and Records.
The `%` variable represents the dependency type and is only available in case there is a dependency provided. 

During the compilation, the function is checked for correctness and the return type should match the type of the expression.

## Extended functions
In addition to the basic functions, Walnut supports behavior-driven functions (or methods) which are assigned to types.
They are a 6-tuple consisting of a target type, a method name, a dependency type, a parameter type, a return type,
and a body in the form of an expression.

#### Example (main syntax)
```walnut
Article->publish(^Null => ArticlePublished) %% [~Clock] :: {
    $.publishDate->SET(%.clock->now);
    ArticlePublished[]
};
```
In addition to the `#` and `%` variables, there is one more: `$`. The `$` variable represents the target type.
It is also the only variable through which the properties of a sealed type can be accessed.
In its core, this syntax is just a syntactic sugar over the basic function syntax. The example above resembles a 
function similar to this one in a C-like language:
```
function article_publish(%: [~Clock], $: Article, #: Null): ArticlePublished {
    $.publishDate = %.clock.now();
    return ArticlePublished();
}
```
One convenience is that the dependency parameter is automatically injected (or curried) using the built-in dependency injection mechanism.

## Casts
Between every two named types there can be a cast function. It is a function that converts a value of one type to a value of another type.
```walnut
Article ==> String :: #.title;  
```

The method *Any->as(Type)* can be used to cast any value to a given target type. In case the cast is proven to be possible
during the compilation, the cast result is of the target type. Otherwise, the expression type is Result<T, CastNotAvailable>.

Internally the cast functions FromType ==> ToType :: Expression are implemented as FromType->asToType(^Null => ToType) :: Expression.

## Providing dependencies
The builtin dependency injection mechanism may require some dependencies to be specified explicitly. In order to do this,
the following syntax can be used:
```walnut
==> ArticleService %% <other dependencies> :: <some code here>
```
This is just a short version of the cast *DependencyContainer ==> ArticleService %% <other dependencies> :: <some code here>*.
In case a value cannot be instantiated, an error of type DependencyContainerError is returned.

## Constructors
The following syntax can be used to define a constructor for a subtype or a sealed type:
```
Article(title: String) %% [~Clock] :: [title: title, publishDate: %.clock->now];
```
Once again this is presented as *Constructor->Article(title: String) %% [~Clock] :: [title: title, publishDate: %.clock->now];*.

## Invariant validators
This is a unique feature of Walnut. There is a way to provide a validator function which in case it explicitly returns an 
error the value construction is rejected and the error is returned instead. It may again be used for both subtypes and sealed types.
Invariant validators and constructors can be used or omitted independently.
```walnut
NotAnOddInteger = :[]; /* define an Atom type for the error */
OddInteger <: Integer @ NotAnOddInteger :: ?whenValueOf(# % 2) is { 0: => Error(NotAnOddInteger[]) };
```
It is important to know that while the constructor can be bypassed by using the `JsonValue->hydrateAs` method, 
the invariant validator is always executed. In case the validator returns an error, the `hydrateAs` method will return a `HydrationError`.

## Default types (syntactic sugar)
In case the return type of an extended function is Any, it can be omitted.
```walnut
Article->returnsAny(^x:String) :: ...some expression;
```
A similar simplification can be applied to the parameter type in case it is Null.
```walnut
Article->acceptsNull(=> String) :: ...some expression;
```

For a function expecting Null and returning Any, both types can be omitted.
```walnut
Article->acceptsNullAndReturnsAny() :: ...some expression;
```

## Calling functions
Simple functions can be called using the following syntax:
```walnut
getLength = ^s: String<1..10> => Integer<1..10> :: s->length;
// call somewhere in the code:
getLength('Hello, World!'); /* = 13 */
```
In case a function accepts Null as parameter type, it may be called without any arguments:
```walnut
getLength = ^Null => Integer<1..10> :: 13;
// call somewhere in the code:
getLength(); /* = 13 */
```
In order to simplify the syntax, calls to functions where the parameter is a record or a tuple can omit the brackets:
```walnut
sum = ^[a: Integer, b: Integer] :: #.a + #.b;
// call somewhere in the code:
sum[a: 1, b: 2]; /* = 3 */
// as a special case, a tuple can be passed instead of a record if the argument count is the same:
sum[1, 2]; /* = 3 */
```

Behavior-driven functions can be called using the following syntax:
```walnut
a = Article('My first article');
/* No brackets required when passing null as parameter */
a->title; /* = 'My first article' */
/* Otherwise brackets are necessary */
a->markAsReadBy(user);
/* As in the simple functions, brackets can be omitted when passing a record */
a->publish[author: user];
```

## Calling constructors
Constructors can be called using the same function call syntax:
```walnut
a = Article('My first article');
b = Book[isbn: 1234567890, title: 'My first book'];
```
Alongside the constructor calls for sealed types and subtypes the only special calls are the ones for the following built-in types:
Error, Mutable

Constructing an error value of type String:
```walnut
myError = Error('Something went wrong'); /* long syntax */
anotherError = @'Something went wrong'; /* short syntax */
```
Constructing a mutable value:
```walnut
myMutable = Mutable(type{Integer}, 5); /* long syntax */
anotherMutable = mutable{Integer, 5}; /* short syntax */
```