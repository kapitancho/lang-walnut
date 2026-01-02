# 14. Module System

## 14.1 Overview

Walnut's module system organizes code into reusable, independently compilable units. Each module corresponds to a source file and declares its dependencies explicitly. The module system supports:

- Explicit module declarations with dependencies
- Package-based module organization
- Configurable module resolution via `nutcfg.json`
- Automatic core module availability
- Multiple file types (`.nut`, `.test.nut`, `.nut.html`)

## 14.2 Module Declaration

### 14.2.1 Basic Module Declaration

Every Walnut source file must start with a module declaration:

```walnut
module module-name:
```

**Example:**
```walnut
module event:

EventListener = ^Nothing => *Null;
EventBus := $[listeners: Array<EventListener>];
```

The module name must match the file path (without the `.nut` extension). For example:
- File: `event.nut` → Module: `module event:`
- File: `http/router.nut` → Module: `module http/router:`
- File: `db/orm/xorm.nut` → Module: `module db/orm/xorm:`

### 14.2.2 Module Declaration with Dependencies

Modules can declare dependencies using the `%%` operator:

```walnut
module module-name %% dependency1, dependency2, dependency3:
```

**Example:**
```walnut
module http/middleware %% http/message, http/request-handler:

HttpMiddleware = ^[request: {HttpRequest}, handler: {HttpRequestHandler}] => {HttpResponse};
```

Dependencies can be listed on a single line or span multiple lines:

```walnut
module http/bundle/composite-router %% http/middleware/cors,
    http/request-handler/not-found, http/request-handler/composite, http/router:

/* module content */
```

### 14.2.3 Module Naming Rules

**Standard modules:**
```walnut
module user-service:           /* File: user-service.nut */
module http/router:            /* File: http/router.nut */
module db/orm/xorm:            /* File: db/orm/xorm.nut */
```

**Core modules** (prefix with `$`):
```walnut
module $core:                  /* File: core-nut-lib/core.nut */
module $http/router:           /* File: core-nut-lib/http/router.nut */
module $db/connection:         /* File: core-nut-lib/db/connection.nut */
```

**Test modules** (suffix with `-test` or use `.test.nut`):
```walnut
module shopping-cart/cart-test:   /* File: shopping-cart/cart.test.nut */
```

## 14.3 Module Dependencies

### 14.3.1 Dependency Syntax

Dependencies can be declared with or without the `$` prefix:

**With `$` prefix (recommended):**
```walnut
module http/router %% $http/message, $http/middleware:
```

**Without prefix:**
```walnut
module shopping-cart/model:

/* No dependencies needed */
```

**Mixed dependencies:**
```walnut
module db/orm/xorm %% $db/core, $db/sql/query-builder, $db/sql/quoter-mysql:
```

### 14.3.2 Dependency Resolution

When a module declares a dependency, the compiler resolves it using the following algorithm:

1. **Core module prefix**: If dependency starts with `$`, prepend `core/` after removing `$`
   - `$http/router` → `core/http/router`
   - `$datetime` → `core/datetime`

2. **Package matching**: Check if the module name starts with a registered package prefix
   - `shopping-cart/model` → Matches package `shopping-cart` → Resolves to configured path

3. **Default source root**: If no package matches, use the default source root
   - `my-app/service` → `{sourceRoot}/my-app/service.nut`

### 14.3.3 Implicit Core Import

The `core` module is implicitly available in all modules without explicit declaration. Types and atoms from `core.nut` are automatically in scope:

```walnut
module my-service:

/* These types are available without importing: */
/* String, Integer, Real, Boolean, Array, Map, etc. */
/* NotANumber, IndexOutOfRange, MapItemNotFound, etc. */

UserId := Integer<1..>;  /* Integer from core */
```

### 14.3.4 Circular Dependencies

Circular dependencies between modules are not allowed. The compiler will detect and report circular dependency chains:

```walnut
/* ❌ Circular dependency error */
module a %% b:
module b %% a:

/* ✅ Correct: Break the cycle */
module a:
module b %% a:
```

## 14.4 Package Configuration

### 14.4.1 nutcfg.json Structure

The `nutcfg.json` file at the project root configures module resolution:

```json
{
  "sourceRoot": "walnut-src",
  "packages": {
    "core": "core-nut-lib",
    "shopping-cart": "walnut-src/shopping-cart"
  }
}
```

**Fields:**
- `sourceRoot`: The default directory for modules not in a named package
- `packages`: A map of package names to their file system paths

### 14.4.2 sourceRoot Configuration

The `sourceRoot` specifies where unpackaged modules are located:

**Example 1: Default source root**
```json
{
  "sourceRoot": "walnut-src"
}
```

Module `my-app/controller` resolves to:
```
walnut-src/my-app/controller.nut
```

**Example 2: Different source root**
```json
{
  "sourceRoot": "src"
}
```

Module `services/user` resolves to:
```
src/services/user.nut
```

### 14.4.3 Package Mapping

Packages allow you to organize related modules in dedicated directories:

```json
{
  "sourceRoot": "walnut-src",
  "packages": {
    "core": "core-nut-lib",
    "shopping-cart": "walnut-src/shopping-cart",
    "http": "lib/http-framework"
  }
}
```

**Resolution examples:**

| Module Name | Resolves To |
|------------|-------------|
| `core/datetime` | `core-nut-lib/datetime.nut` |
| `core/http/router` | `core-nut-lib/http/router.nut` |
| `shopping-cart/model` | `walnut-src/shopping-cart/model.nut` |
| `http/server` | `lib/http-framework/server.nut` |
| `my-app/service` | `walnut-src/my-app/service.nut` |

**Important:** Package names must match exactly including the trailing `/` in resolution. A module named `core` (without `/`) would not match the `core` package.

### 14.4.4 Default Configuration

If `nutcfg.json` is missing or incomplete, defaults are used:

```json
{
  "sourceRoot": "walnut-src",
  "packages": {
    "core": "core-nut-lib"
  }
}
```

## 14.5 Module Resolution Algorithm

The complete module resolution algorithm:

**Input:** Module name (e.g., `$http/router`, `shopping-cart/model`, `my-service`)

**Steps:**

1. **Handle special prefixes:**
   - If starts with `$`: Remove `$`, prepend `core/`
     - `$http/router` → `core/http/router`
   - If starts with `?`: Remove `?`, append `-test`
     - `?shopping-cart/cart` → `shopping-cart/cart-test`

2. **Match against packages:**
   - For each package in `packages` configuration:
     - If module name starts with `{package}/`:
       - Return: `{package-path}/{remaining-path}.nut`
   - Example: `shopping-cart/model` with package `shopping-cart: walnut-src/shopping-cart`
     - Returns: `walnut-src/shopping-cart/model.nut`

3. **Use source root:**
   - If no package matched:
     - Return: `{sourceRoot}/{module-name}.nut`
   - Example: `my-service` with sourceRoot `walnut-src`
     - Returns: `walnut-src/my-service.nut`

**Resolution examples:**

```json
{
  "sourceRoot": "walnut-src",
  "packages": {
    "core": "core-nut-lib",
    "shopping-cart": "walnut-src/shopping-cart"
  }
}
```

| Module Name | Resolved Path |
|------------|---------------|
| `$core` | `core-nut-lib/core.nut` |
| `$datetime` | `core-nut-lib/datetime.nut` |
| `$http/router` | `core-nut-lib/http/router.nut` |
| `$db/orm/xorm` | `core-nut-lib/db/orm/xorm.nut` |
| `shopping-cart/model` | `walnut-src/shopping-cart/model.nut` |
| `?shopping-cart/cart` | `walnut-src/shopping-cart/cart-test.nut` |
| `my-app/service` | `walnut-src/my-app/service.nut` |
| `demo-tpl-model` | `walnut-src/demo-tpl-model.nut` |

## 14.6 File Types

### 14.6.1 Regular Modules (.nut)

Standard Walnut source files containing type definitions, methods, constructors, and casts.

**Example: `core-nut-lib/datetime.nut`**
```walnut
module $datetime:

Clock := ();
InvalidDate := ();
InvalidTime := ();

Date := #[year: Integer, month: Integer<1..12>, day: Integer<1..31>] @ InvalidDate :: {
    /* validation logic */
};

Time := #[hour: Integer<0..23>, minute: Integer<0..59>, second: Integer<0..59>];
DateAndTime := #[date: Date, time: Time];

Date ==> String :: [
    $year->asString,
    $month->asString->padLeft[length: 2, padString: '0'],
    $day->asString->padLeft[length: 2, padString: '0']
]->combineAsString('-');
```

### 14.6.2 Test Modules (.test.nut)

Test files contain test cases for unit testing. They typically depend on `$test/runner`:

**Example: `shopping-cart/cart.test.nut`**
```walnut
module shopping-cart/cart-test %% $test/runner, shopping-cart/model:

==> TestCases :: {
    getShoppingCart = ^ => ShoppingCart :: ShoppingCart();
    getShirt = ^ => Shirt :: Shirt[
        id: ProductId!'shirt-1',
        model: ProductTitle!'Gucci Pro',
        price: ProductPrice!100.0,
        size: ShirtSize.M,
        color: ShirtColor.Black
    ];
    [
        ^ => TestResult :: {
            TestResult['A new shopping cart is empty', 0, ^ :: getShoppingCart()->itemsCount]
        },
        ^ => TestResult :: {
            shoppingCart = getShoppingCart();
            shirt = getShirt();
            shoppingCart->addItem[shirt, ShoppingCartQuantity!1];
            TestResult['Add a shirt to a shopping cart', 1, ^ :: shoppingCart->itemsCount]
        }
    ]
};
```

**Key characteristics:**
- Must export `TestCases` type
- Usually named with `-test` suffix or `.test.nut` extension
- Can be run by test runner
- Used for automated testing

### 14.6.3 Template Modules (.nut.html)

HTML templates with embedded Walnut expressions:

**Example: `demo-tpl-product-list.nut.html`**
```html
<!-- ProductList %% demo-tpl-model -->
<h3>Product List</h3>
<ul>
    <!-- $products->map(^Product => Any :: { -->
        <li>
            <!--{#name}--> : $<!--[#price->roundAsDecimal(2)]-->
        </li>
    <!-- }); -->
</ul>
```

**Key characteristics:**
- HTML with Walnut code in HTML comments
- Declare dependencies in first comment: `<!-- ModuleName %% dep1, dep2 -->`
- Compiled to regular modules that produce `Template` values
- Used for server-side rendering

## 14.7 Module Scope

### 14.7.1 Top-Level Declarations

Modules can contain the following top-level declarations:

**Type definitions:**
```walnut
module my-app:

UserId := Integer<1..>;                          /* Type alias */
User := $[id: UserId, name: String<1..100>];    /* Sealed type */
OrderStatus := (Pending, Shipped, Delivered);   /* Enumeration */
NotFound := ();                                  /* Atom */
```

**Methods:**
```walnut
User->name(=> String) :: $name;
User ==> String :: 'User #' + $id->asString + ': ' + $name;
```

**Constructors:**
```walnut
User := #[id: UserId, name: String<1..100>] :: {
    /* constructor logic */
};
```

**Casts:**
```walnut
JsonValue ==> User @ HydrationError :: {
    /* hydration logic */
};
```

**Entry points (CLI only):**
```walnut
=> {
    /* CLI entry point logic */
};
```

### 14.7.2 No Global Variables

Walnut modules do not support global variables. All state must be encapsulated in:
- Type definitions
- Constructor parameters
- Method parameters
- Function closures

**❌ Not allowed:**
```walnut
module my-app:

globalCounter = 0;  /* Error: Global variables not supported */
```

**✅ Correct approach:**
```walnut
module my-app:

Counter := $[value: Mutable<Integer<0..>>];
Counter() :: [value: mutable{Integer<0..>, 0}];
Counter->increment(=> Integer<0..>) :: {
    newValue = $value->value + 1;
    $value->SET(newValue)
};
```

### 14.7.3 Implicit Core Import

All modules have implicit access to the `core` module types and atoms without explicit import:

**From core.nut:**
```walnut
/* Primitive types */
String, Integer, Real, Boolean, Null

/* Collection types */
Array, Map, Set, Tuple, Record

/* Error atoms */
NotANumber, IndexOutOfRange, MapItemNotFound, ItemNotFound

/* Type utilities */
Constructor, DependencyContainer, JsonValue

/* And many more... */
```

**Usage in any module:**
```walnut
module my-service:

/* No need to import core */
UserId := Integer<1..>;                    /* Integer from core */
UserName := String<1..100>;                /* String from core */
Users := Map<String:UserName>;             /* Map with string keys and UserName values */

validateId = ^id: Any => Result<UserId, CastNotAvailable> :: {
    id->as(`UserId)  /* CastNotAvailable from core */
};
```

## 14.8 Examples

### 14.8.1 Simple Module Without Dependencies

**File: `walnut-src/event.nut`**
```walnut
module event:

EventListener = ^Nothing => *Null;
EventBus := $[listeners: Array<EventListener>];
```

**Characteristics:**
- No dependencies (besides implicit core)
- Defines two types
- Self-contained functionality

### 14.8.2 Module with Core Dependencies

**File: `core-nut-lib/http/router.nut`**
```walnut
module $http/router %% $http/message, $http/middleware:

HttpLookupRouterPath = [path: String, type: Type, method: ?HttpRequestMethod];
HttpLookupRouterMapping = Array<HttpLookupRouterPath>;

HttpLookupRouter := $[routerMapping: HttpLookupRouterMapping];
HttpLookupRouter ==> HttpMiddleware %% [~DependencyContainer, ~HttpResponseBuilder] :: {
    run = ^[request: {HttpRequest}, type: Type] => {HttpResponse} :: {
        handler = %dependencyContainer->valueOf(#type);
        rh = handler->as(`HttpRequestHandler);
        ?whenIsError(rh) {
            %httpResponseBuilder(500)->withBody('Invalid handler type')
        } ~ {
            rh(#request->shape(`HttpRequest))
        }
    };

    ^[request: {HttpRequest}, handler: {HttpRequestHandler}] => {HttpResponse} :: {
        /* routing logic */
    }
};
```

**Characteristics:**
- Core module (starts with `$`)
- Depends on other core modules
- Uses dependency injection (`%% [~DependencyContainer, ~HttpResponseBuilder]`)
- Complex functionality with multiple types

### 14.8.3 Module with Package Dependencies

**File: `walnut-src/shopping-cart/model.nut`**
```walnut
module shopping-cart/model:

ProductId := String<1..>;
ProductTitle := String<1..>;
ProductPrice := Real<0..>;
ShoppingCartQuantity := Integer<1..>;

ShoppingCartProduct = [id: ProductId, title: String, price: ProductPrice];
ShoppingCartItem = [product: Shape<ShoppingCartProduct>, quantity: Mutable<ShoppingCartQuantity>];
ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];

ShoppingCart() :: [items: mutable{Map<ShoppingCartItem>, [:]}];

ProductNotInCart := #[productId: ProductId];

ShoppingCart->item(^productId: ProductId => Result<ShoppingCartItemData, ProductNotInCart>) :: {
    existingItem = $items->value->item(productId->asString);
    ?whenTypeOf(existingItem) is {
        `ShoppingCartItem: [product: existingItem.product, quantity: existingItem.quantity->value],
        ~: @ProductNotInCart[productId]
    }
};

ShoppingCart->addItem(^[product: Shape<ShoppingCartProduct>, quantity: ShoppingCartQuantity] => ShoppingCartQuantity) :: {
    /* implementation */
};
```

**Characteristics:**
- Part of `shopping-cart` package
- No explicit dependencies (only core)
- Defines domain model
- Uses mutable state

### 14.8.4 Module Dependency Chain

**Chain: `composite-router` → `cors`, `not-found`, `composite`, `router`**

**File: `core-nut-lib/http/bundle/composite-router.nut`**
```walnut
module $http/bundle/composite-router %% $http/middleware/cors,
    $http/request-handler/not-found, $http/request-handler/composite, $http/router:

==> HttpCompositeRequestHandler %% [
    ~HttpNotFoundHandler,
    ~HttpLookupRouter,
    ~HttpCorsMiddleware
] :: HttpCompositeRequestHandler[
    defaultHandler: %httpNotFoundHandler,
    middlewares: [%httpCorsMiddleware, %httpLookupRouter]
];
```

**Dependencies:**
1. `$http/middleware/cors` → `core-nut-lib/http/middleware/cors.nut`
2. `$http/request-handler/not-found` → `core-nut-lib/http/request-handler/not-found.nut`
3. `$http/request-handler/composite` → `core-nut-lib/http/request-handler/composite.nut`
4. `$http/router` → `core-nut-lib/http/router.nut`

Each dependency may have its own dependencies, forming a dependency graph.

### 14.8.5 Test Module

**File: `walnut-src/shopping-cart/cart.test.nut`**
```walnut
module shopping-cart/cart-test %% $test/runner, shopping-cart/model:

==> TestCases :: {
    getShoppingCart = ^ => ShoppingCart :: ShoppingCart();
    getShirt = ^ => Shirt :: Shirt[
        id: ProductId!'shirt-1',
        model: ProductTitle!'Gucci Pro',
        price: ProductPrice!100.0,
        size: ShirtSize.M,
        color: ShirtColor.Black
    ];
    [
        ^ => TestResult :: {
            TestResult['A new shopping cart is empty', 0, ^ :: getShoppingCart()->itemsCount]
        },
        ^ => TestResult :: {
            shoppingCart = getShoppingCart();
            shirt = getShirt();
            shoppingCart->addItem[shirt, ShoppingCartQuantity!1];
            TestResult['Add a shirt to a shopping cart', 1, ^ :: shoppingCart->itemsCount]
        }
    ]
};
```

**Characteristics:**
- Test module (suffix `-test`)
- Depends on test runner and module under test
- Returns `TestCases` array
- Each test case is a function returning `TestResult`

## 14.9 Best Practices

### 14.9.1 Module Organization

**Organize by feature:**
```
walnut-src/
  shopping-cart/
    model.nut           - Domain types
    cart.test.nut       - Tests
  user-service/
    types.nut           - User-related types
    repository.nut      - Data access
    controller.nut      - HTTP handlers
```

**Separate concerns:**
```
core-nut-lib/
  http/
    message.nut         - Request/Response types
    router.nut          - Routing logic
    middleware.nut      - Middleware interface
    middleware/
      cors.nut          - CORS middleware
```

### 14.9.2 Dependency Management

**Minimize dependencies:**
```walnut
/* ✅ Good: Only necessary dependencies */
module user-service %% $db/connection:

/* ❌ Bad: Unnecessary dependencies */
module user-service %% $db/connection, $http/router, $tpl, $datetime:
```

**Declare all dependencies explicitly:**
```walnut
/* ✅ Good: All dependencies declared */
module http/autowire/response-helper %% $http/autowire, $tpl:

/* ❌ Bad: Missing dependency (will fail at compile time) */
module http/autowire/response-helper %% $http/autowire:
/* Uses $tpl types but doesn't declare dependency */
```

**Use core prefix consistently:**
```walnut
/* ✅ Good: Consistent use of $ prefix */
module my-app %% $http/router, $db/connection:

/* ⚠️ Less clear: Mixing styles */
module my-app %% http/router, $db/connection:
```

### 14.9.3 Avoid Circular Dependencies

**Problem:**
```walnut
/* user-service.nut */
module user-service %% order-service:

/* order-service.nut */
module order-service %% user-service:
/* Error: Circular dependency */
```

**Solution 1: Extract common types:**
```walnut
/* types.nut */
module types:
UserId := Integer<1..>;
OrderId := Integer<1..>;

/* user-service.nut */
module user-service %% types:

/* order-service.nut */
module order-service %% types:
```

**Solution 2: Unify modules:**
```walnut
/* user-order-service.nut */
module user-order-service:
/* Combine related functionality */
```

### 14.9.4 Package Structure

**Keep related modules together:**
```json
{
  "packages": {
    "core": "core-nut-lib",
    "shopping-cart": "walnut-src/shopping-cart",
    "user-management": "walnut-src/user-management"
  }
}
```

**Use meaningful package names:**
```walnut
/* ✅ Good: Clear package structure */
module shopping-cart/model:
module shopping-cart/repository:
module user-management/authentication:

/* ❌ Bad: Flat structure */
module cart-model:
module cart-repository:
module auth:
```

### 14.9.5 Module Naming

**Match file paths exactly:**
```
File: http/router.nut
Module: module http/router:  ✅

File: http/router.nut
Module: module router:  ❌
```

**Use kebab-case for multi-word names:**
```walnut
module user-service:           ✅
module user_service:           ❌ (avoid underscores)
module userService:            ❌ (avoid camelCase)
```

**Test modules suffix with `-test`:**
```walnut
module shopping-cart/cart-test:     ✅
module shopping-cart/cartTest:      ❌
module shopping-cart/test-cart:     ❌
```

### 14.9.6 Module Size

**Keep modules focused:**
```walnut
/* ✅ Good: Single responsibility */
module http/message:
/* Only HTTP message types */

module http/router:
/* Only routing logic */

/* ❌ Bad: Everything in one module */
module http:
/* Messages, routing, middleware, handlers, etc. */
```

**Split large modules:**
```
http/
  message.nut           - Request/Response types
  router.nut            - Routing
  middleware.nut        - Middleware interface
  middleware/
    cors.nut            - CORS middleware
    auth.nut            - Auth middleware
```

### 14.9.7 Testing Strategy

**Create test modules for complex logic:**
```walnut
/* shopping-cart/model.nut */
module shopping-cart/model:
ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];

/* shopping-cart/cart.test.nut */
module shopping-cart/cart-test %% $test/runner, shopping-cart/model:
==> TestCases :: { /* tests */ };
```

**Keep tests close to implementation:**
```
shopping-cart/
  model.nut
  cart.test.nut
  repository.nut
  repository.test.nut
```

## 14.10 Advanced Topics

### 14.10.1 Module Resolution Cache

The compiler caches resolved module paths for performance. If you change `nutcfg.json`, you may need to rebuild.

### 14.10.2 Module Compilation Order

Modules are compiled in dependency order:
1. Modules with no dependencies first
2. Modules depending on compiled modules next
3. Continues until all modules are compiled

This ensures types are available when needed.

### 14.10.3 Module Visibility

All types, methods, constructors, and casts declared in a module are public and available to modules that depend on it. There is no concept of private or internal declarations.

To encapsulate implementation details:
- Use separate modules for public API and implementation
- Document which types are part of the public API
- Use naming conventions (e.g., prefix with `_` for internal types)

### 14.10.4 Re-exports

Walnut does not support re-exporting types from other modules. Each module must explicitly depend on the modules it uses:

```walnut
/* ❌ Cannot re-export */
module a %% b:
/* Cannot expose types from b */

/* ✅ Must depend directly */
module c %% b:  /* Not: %% a */
```

## 14.11 Summary

The Walnut module system provides:

1. **Explicit dependencies**: All module dependencies must be declared
2. **Package-based organization**: Configure packages in `nutcfg.json`
3. **Multiple file types**: Regular, test, and template modules
4. **Automatic core import**: Core types available everywhere
5. **Path-based naming**: Module names match file paths
6. **Compile-time resolution**: All dependencies resolved at compile time
7. **No circular dependencies**: Enforced by the compiler
8. **Focused scope**: Types, methods, constructors, casts only

This design ensures modularity, maintainability, and compile-time safety while keeping the module system simple and predictable.
