# 20. Templates

## Overview

Walnut supports HTML templates through `.nut.html` files, allowing embedding of Walnut expressions directly in HTML markup. Templates enable server-side rendering and are particularly useful for building web applications with type-safe, compile-time checked view logic.

## 20.1 Template File Format

### 20.1.1 File Extension

Template files use the `.nut.html` extension.

**Examples:**
- `product-list.nut.html`
- `user-profile.nut.html`
- `dashboard.nut.html`

### 20.1.2 Template Declaration

Templates are declared using HTML comments with the template name and optional dependencies.

**Syntax:**
```html
<!-- TemplateName -->
<!-- TemplateName %% dependency1, dependency2 -->
```

**Examples:**
```html
<!-- ProductList -->

<!-- ProductPage %% demo-tpl-model -->

<!-- UserProfile %% user-service -->
```

## 20.2 Template Syntax

### 20.2.1 Expression Embedding

Walnut expressions are embedded in templates using special comment syntax.

**Expression syntax:** `<!--{expression}-->`

**Examples:**
```html
<!-- Simple variable -->
<p><!--{$name}--></p>

<!-- Property access -->
<h1><!--{$user.name}--></h1>

<!-- Method call -->
<span><!--{$price->roundAsDecimal(2)}--></span>

<!-- Compound expression -->
<div><!--{$firstName + ' ' + $lastName}--></div>
```

### 20.2.2 Template Inclusion

Other templates can be included using the `%` syntax.

**Include syntax:** `<!--% templateVariable %-->`

**Example:**
```html
<main>
    <!--% $productList %-->
</main>

<aside>
    <!--% $sidebar %-->
</aside>
```

### 20.2.3 Loops and Iteration

Loops are implemented using Walnut expressions that generate HTML.

**Syntax:**
```html
<!-- array->map(^item :: { -->
    <!-- HTML for each item -->
<!-- }); -->
```

**Example:**
```html
<ul>
    <!-- $products->map(^ ~Product :: { -->
        <li>
            <!--{product.name}--> : $<!--{product.price->roundAsDecimal(2)}-->
        </li>
    <!-- }); -->
</ul>
```

### 20.2.4 Variable Syntax in Templates

Templates have access to special variables:

- **`$`** - The target value (template data)
- **`$property`** - Access property of target (e.g., `$name`, `$email`)
- **`#`** - The parameter in lambda expressions
- **`#property`** - Access property of parameter (e.g., `#id`, `#price`)

## 20.3 Complete Template Example

### 20.3.1 Model Definition

```walnut
/* demo-tpl-model.nut */
module demo-tpl-model:

Product = [id: Integer, name: String, price: Real];
ProductList = [products: Array<Product>];
ProductPage = [productList: ProductList, title: String];
```

### 20.3.2 Product List Template

```html
<!-- demo-tpl-product-list.nut.html -->
<!-- ProductList %% demo-tpl-model -->
<h3>Product List</h3>
<ul>
    <!-- $products->map(^ ~Product :: { -->
        <li>
            <!--{product.name}--> : $<!--{product.price->roundAsDecimal(2)}-->
        </li>
    <!-- }); -->
</ul>
```

**Explanation:**
- Template name: `ProductList`
- Depends on: `demo-tpl-model` module
- `$products` accesses the `products` field of the target
- `->map(^ ~Product :: { ... })` iterates over products
- `#name` and `#price` access fields of each Product
- `#price->roundAsDecimal(2)` formats the price

### 20.3.3 Product Page Template

```html
<!-- demo-tpl-product-page.nut.html -->
<!-- ProductPage %% demo-tpl-model -->
<header>
    <h1><!--{$title}--></h1>
</header>
<main>
    <!--% $productList %-->
</main>
<footer>&copy; all rights reserved</footer>
```

**Explanation:**
- Template name: `ProductPage`
- Depends on: `demo-tpl-model` module
- `$title` accesses the title field
- `<!--% $productList %-->` includes the ProductList template
- `$productList` is passed as data to the included template

### 20.3.4 Using Templates

```walnut
/* demo-tpl.nut */
module demo-tpl %% $tpl, demo-tpl-product-list, demo-tpl-product-page:

::> {
    /* Create test data */
    getProductList = ^Null => ProductList :: [products: [
        [id: 1, name: 'Apple <Granny Smith>', price: 1.99],
        [id: 2, name: 'Banana', price: 0.99],
        [id: 3, name: 'Cherry', price: 2.99]
    ]];

    getProductPage = ^Null => ProductPage :: {
        [productList: getProductList(), title: 'This is the product page']
    };

    /* Render template */
    myFn = ^Null => Result<String, Any> %% [~TemplateRenderer] :: {
        %templateRenderer => render(getProductPage())
    };

    x = myFn();
    ?whenTypeOf(x) is {
        `String: x->OUT_TXT,
        ~: x->printed
    }
};
```

## 20.4 Template Features

### 20.4.1 HTML Escaping

By default, expressions are HTML-escaped to prevent XSS attacks.

**Example:**
```html
<!-- $userInput contains: <script>alert('XSS')</script> -->
<p><!--{$userInput}--></p>
<!-- Output: &lt;script&gt;alert('XSS')&lt;/script&gt; -->
```

### 20.4.2 Conditional Rendering

Use Walnut's conditional expressions for conditional rendering.

**Example:**
```html
<!-- ?when($isLoggedIn) { -->
    <div class="user-menu">
        <span>Welcome, <!--{$userName}-->!</span>
    </div>
<!-- } ~ { -->
    <a href="/login">Log In</a>
<!-- }; -->
```

### 20.4.3 Nested Templates

Templates can include other templates, creating a component hierarchy.

**Example:**

*** Models: ***
```
module my-page-model:
Header := ();
Footer := ();
Page := [title: String, content: String, header: Header, footer: Footer];
```

**Header template:**
```html
<!-- Header %% my-page-model -->
<header>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
    </nav>
</header>
```

**Footer template:**
```html
<!-- Footer %% my-page-model -->
<footer>
    <p>&copy; 2025 My Company</p>
</footer>
```

**Page template:**
```html
<!-- Page %% my-page-model -->
<!DOCTYPE html>
<html>
<head>
    <title><!--{$title}--></title>
</head>
<body>
    <!--% $header %-->
    <main>
        <!--{$content}-->
    </main>
    <!--% $footer %-->
</body>
</html>
```

### 20.4.4 Complex Expressions

Templates support any Walnut expression.

**Examples:**
```html
<!-- Arithmetic -->
<p>Total: $<!--{$price * $quantity}--></p>

<!-- String concatenation -->
<p><!--{$firstName + ' ' + $lastName}--></p>

<!-- Method chains -->
<p><!--{$text->trim->toLowerCase->capitalize}--></p>

<!-- Conditional expression -->
<span class="<!--{?when($isActive) { 'active' } ~ { 'inactive' }}-->">
    Status
</span>

<!-- Array operations -->
<p>Count: <!--{$items->length}--></p>
<p>First: <!--{$items->item(0)}--></p>
```

## 20.5 Advanced Template Patterns

### 20.5.1 List Rendering with Index

```html
<!-- Numbered list -->
<ol>
    <!-- $items->mapIndexValue(^[index: Integer, value: Item] :: { -->
        <li>
            Item #<!--{#index + 1}-->: <!--{#value.name}-->
        </li>
    <!-- }); -->
</ol>
```

### 20.5.2 Table Rendering

```html
<!-- User table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <!-- $users->map(^ ~User :: { -->
            <tr>
                <td><!--{user.id}--></td>
                <td><!--{user.name}--></td>
                <td><!--{user.email}--></td>
            </tr>
        <!-- }); -->
    </tbody>
</table>
```

### 20.5.3 Conditional CSS Classes

```html
<div class="product <!--{?when($inStock) { 'available' } ~ { 'out-of-stock' }}-->">
    <h3><!--{$productName}--></h3>
    <p>Price: $<!--{$price}--></p>
    <!-- ?when($inStock) { -->
        <button>Add to Cart</button>
    <!-- } ~ { -->
        <span class="badge">Out of Stock</span>
    <!-- }; -->
</div>
```

### 20.5.4 Nested Data Structures

```html
<!-- Nested categories and products -->
<div class="categories">
    <!-- $categories->map(^ ~Category :: { -->
        <div class="category">
            <h2><!--{category.name}--></h2>
            <div class="products">
                <!-- category.products->map(^ ~Product :: { -->
                    <div class="product">
                        <h3><!--{product.name}--></h3>
                        <p>$<!--{product.price}--></p>
                    </div>
                <!-- }); -->
            </div>
        </div>
    <!-- }); -->
</div>
```

### 20.5.5 Form Rendering

```html
<!-- User form -->
<form method="POST" action="/users">
    <div class="field">
        <label>Name:</label>
        <input type="text" name="name" value="<!--{$user.name}-->" />
    </div>

    <div class="field">
        <label>Email:</label>
        <input type="email" name="email" value="<!--{$user.email}-->" />
    </div>

    <div class="field">
        <label>Role:</label>
        <select name="role">
            <!-- $availableRoles->map(^ ~Role :: { -->
                <option value="<!--{role.value}-->"
                        <!--{?when(role.value == $user.role) { 'selected' } ~ { '' }}-->
                >
                    <!--{role.label}-->
                </option>
            <!-- }); -->
        </select>
    </div>

    <button type="submit">Save</button>
</form>
```

## 20.6 Template Rendering

### 20.6.1 TemplateRenderer Dependency

Templates are rendered using the `TemplateRenderer` dependency.

**Usage:**
```walnut
renderPage = ^data: PageData => Result<String, Any> %% [~TemplateRenderer] :: {
    %templateRenderer => render(data)
};
```

### 20.6.2 Type-Safe Rendering

Templates are type-checked at compile time. The data passed to a template must match the expected type.

**Example:**
```walnut
/* Template expects ProductPage */
<!-- ProductPage %% demo-tpl-model -->

/* Must pass ProductPage data */
pageData = [
    productList: getProductList(),
    title: 'My Store'
];  /* Type: ProductPage */

html = %templateRenderer => render(pageData);
```

### 20.6.3 Rendering Pipeline

```walnut
module web-app %% [~TemplateRenderer]:

Page = [title: String, content: String];

renderPage = ^Page => Result<String, Any> :: {
    %templateRenderer => render($)
};

handleRequest = ^Request => Response :: {
    /* Build page data */
    page = Page[
        title: 'Home',
        content: 'Welcome!'
    ];

    /* Render to HTML */
    html = renderPage(page);

    /* Create response */
    Response[
        status: 200,
        headers: [contentType: 'text/html'],
        body: html
    ]
};

==> RequestHandler :: handleRequest;
```

## 20.7 Best Practices

### 20.7.1 Keep Templates Simple

```html
<!-- Good: Simple, focused template -->
<!-- ProductCard -->
<div class="product">
    <h3><!--{$name}--></h3>
    <p>$<!--{$price}--></p>
</div>

<!-- Avoid: Complex logic in templates -->
```

### 20.7.2 Use Strong Types

```walnut
/* Good: Define explicit types for template data */
ProductListData = [
    products: Array<Product>,
    title: String
];

<!-- ProductList expects ProductListData -->

/* Avoid: Using Any or untyped data */
```

### 20.7.3 Separate Concerns

```walnut
/* Good: Keep business logic in modules */
module product-service:

formatPrice = ^Real => String ::
    '$' + #->roundAsDecimal(2)->asString;

getProducts = ^Null => Array<Product> ::
    /* ... */;

/* Template just displays data */
<!-- Template uses formatPrice -->
<p><!--{$price->formatPrice}--></p>
```

### 20.7.4 Compose Templates

```html
<!-- Good: Small, reusable templates -->
<!-- ProductCard -->
<div class="product">
    <h3><!--{$name}--></h3>
    <p>$<!--{$price}--></p>
</div>

<!-- ProductGrid -->
<div class="grid">
    <!-- $products->map(^ ~Product :: { -->
        <!--% ProductCard(product) %-->
    <!-- }); -->
</div>

<!-- Avoid: Monolithic templates -->
```

### 20.7.5 Handle Edge Cases

```html
<!-- Good: Handle empty lists -->
<!-- ?when($products->length == 0) { -->
    <p>No products available</p>
<!-- } ~ { -->
    <ul>
        <!-- $products->map(^ ~Product :: { -->
            <li><!--{product.name}--></li>
        <!-- }); -->
    </ul>
<!-- }; -->
```

### 20.7.6 Escape User Input

```html
<!-- Good: Expressions are auto-escaped -->
<p><!--{$userInput}--></p>

<!-- Safe: HTML entities are escaped -->
<!-- Input: <script>alert('XSS')</script> -->
<!-- Output: &lt;script&gt;alert('XSS')&lt;/script&gt; -->
```

## 20.8 Template Organization

### 20.8.1 Directory Structure

```
project/
├── templates/
│   ├── layouts/
│   │   ├── main.nut.html
│   │   └── admin.nut.html
│   ├── components/
│   │   ├── header.nut.html
│   │   ├── footer.nut.html
│   │   └── product-card.nut.html
│   └── pages/
│       ├── home.nut.html
│       ├── products.nut.html
│       └── user-profile.nut.html
└── modules/
    └── template-models.nut
```

### 20.8.2 Shared Models

```walnut
/* template-models.nut */
module template-models:

/* Layout models */
MainLayout = [
    title: String,
    content: String,
    user: ?User
];

/* Component models */
HeaderData = [user: ?User];
FooterData = [year: Integer];

/* Page models */
HomePage = [
    featured: Array<Product>,
    recent: Array<Product>
];

ProductPage = [
    product: Product,
    related: Array<Product>
];
```

## 20.9 Testing Templates

### 20.9.1 Template Tests

```walnut
test $product-list-template %% [~TemplateRenderer]:

Product = [id: Integer, name: String, price: Real];
ProductList = [products: Array<Product>];

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Render empty product list',
        expected: '<h3>Product List</h3>\n<ul>\n</ul>',
        actual: ^ :: {
            data = ProductList[products: []];
            %templateRenderer => render(data)
        },
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Render product list with items',
        expected: /* expected HTML */,
        actual: ^ :: {
            data = ProductList[products: [
                Product[id: 1, name: 'Apple', price: 1.99],
                Product[id: 2, name: 'Banana', price: 0.99]
            ]];
            %templateRenderer => render(data)
        },
        after: ^ :: null
    ]
];
```

## Summary

Walnut's template system provides:

- **`.nut.html` files** for HTML templates
- **Expression embedding** with `<!--{expr}-->`
- **Template inclusion** with `<!--% template %-->`
- **Loop support** using `->map` and other collection methods
- **Conditional rendering** with `?when` expressions
- **Type safety** with compile-time checking
- **HTML escaping** by default
- **Composition** through template inclusion
- **Dependency injection** support

Key features:
- Type-safe template data
- Compile-time template validation
- Full Walnut expression support
- Automatic HTML escaping
- Component-based architecture
- Integration with dependency injection

Best practices include:
- Keep templates simple and focused
- Use strong types for template data
- Separate business logic from presentation
- Compose templates for reusability
- Handle edge cases (empty lists, null values)
- Organize templates by purpose (layouts, components, pages)

This template system enables building server-side rendered web applications with the same type safety and functional programming principles as the rest of Walnut.
