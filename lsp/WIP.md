# Walnut LSP — Work-In-Progress Plan

## Folder structure

```
lsp/
  WIP.md             ← this file
  client/            ← VS Code extension (TypeScript / npm)

src/Almond/LSP/      ← PHP LSP server (JSON-RPC over stdio)

bin/
  almond-lsp         ← bash wrapper
  almond-lsp.php     ← PHP entry point
```

---

## What the Almond API already gives us (no changes needed)

| Area | API | Notes |
|------|-----|-------|
| Source injection | `InMemorySourceFinder(array<string,string>)` | Perfect for "live" in-editor content |
| Source chaining | `CompositeSourceFinder` | Layer in-memory over file-based |
| Compilation | `Compiler::builder()->withAddedSourceFinder()->compile()` | Returns `CompiledProgram\|CompilationFailure` |
| Diagnostics | `CompilationFailure->errors` → `errorType`, `errorMessage`, `sourceLocations` | Direct LSP `publishDiagnostics` |
| Source locations | `SourceLocation` → `moduleName`, `startPosition(line,col,offset)`, `endPosition` | 1-based lines, 1-based columns |
| Element → location | `NodeCodeMapper->getSourceLocation($element)` | Forward direction only |
| Source override | `Compiler->withAddedSourceFinder()` / `withStartModule()` | Lets LSP serve live code |

---

## Gaps and their status

### ✅ Gap 1 — CLOSED: Reverse positional lookup (offset → element)

**What was implemented:**

New interface `ProgramBuilder/Blueprint/PositionalLocator.php`:
```php
interface PositionalLocator {
    /**
     * Returns engine elements whose source range covers $offset,
     * ordered from narrowest (most specific) to widest.
     * @return list<Expression|Type|Value|FunctionBody|UserlandMethod>
     */
    public function elementsAtOffset(string $moduleName, int $offset): array;
}
```

**`NodeCodeMapper`** now implements `CodeMapper & SourceNodeLocator & PositionalLocator`.
A parallel `$positionalIndex` array is maintained alongside the existing `WeakMap`:
- Built in `mapNode()`: appends `[startOffset, endOffset, element]` per module
- Queried in `elementsAtOffset()`: O(n) scan → sorted narrowest-first by range size

This is the prerequisite for hover, go-to-definition, and cursor-position-based completion.

---

### ✅ Gap 2 + 3 — CLOSED: Type annotation + variable scope (unified)

**What was implemented:**

Two new Blueprint interfaces in `Engine/Blueprint/Program/Validation/`:

```php
// Injection point — passed into Compiler::withValidationResultCollector()
interface ValidationResultCollector {
    public function collect(ValidationContext $param): void;
}

// Query interface — implemented by the same class
interface ValidationContextScope {
    public function typeOf(Expression|FunctionBody $expression): Type|null;
    public function scopeAt(Expression|FunctionBody $expression): VariableScope|null;
}
```

`ValidationContext::withExpressionType()` and `::withAddedVariableTypes()` each call
`$this->validationResultCollector->collect($result)` after cloning themselves.

**`BacktraceValidationResultCollector`** implements both interfaces. On `collect()` it calls
`debug_backtrace()` to walk the PHP call stack and identify the surrounding `Expression` or
`FunctionBody` objects — storing a `WeakMap<Expression|FunctionBody, ValidationContext>`.
No call-site modifications in individual expressions were required.

**`NoopValidationResultCollector`** does nothing (default, used when not in LSP mode).

**Compiler injection:**
```php
$compiler->withValidationResultCollector($collector);
```

**What this unlocks:**
- `typeOf($expression)` → the `Type` inferred for any expression
- `scopeAt($expression)` → the `VariableScope` (all variable names + types) visible at
  any expression or FunctionBody entry point

---

### ⚠️ Gap 4 — PARTIAL: Method enumeration

`MethodFinder` supports point-lookup only. For `->` completion the LSP needs all methods
for a given type.

**Workaround implemented:** `userlandMethodRegistry->allMethods()` provides all userland
method names. For each name, `methodContext->methodForType($targetType, $methodName)` checks
BOTH native AND userland registries and returns `UnknownMethod` if not applicable.

By iterating userland method names and filtering via `methodForType()`, we get a list of
applicable methods. **Native-only methods** (those with no userland counterpart of the same
name) are **not enumerable** — `NativeMethodRegistry` has no iteration API.

This covers the vast majority of useful completions in practice.

---

## Document storage strategy

Three layers per open file (keyed by URI / module name):

| Layer | Updated when | Used for |
|-------|-------------|----------|
| **live** | `textDocument/didChange` (every keystroke) | Current file compilation |
| **lastParsed** | Last successful parse (no parse errors) | Syntactic features (tokens, structure) |
| **lastValid** | Last full compilation with zero errors | Dependency of other files during compilation |

When compiling module M:
- M itself → served from `live`
- All other modules → served from their `lastValid` (falls back to disk)

---

## LSP feature map (current status)

| Feature | Status | Notes |
|---------|--------|-------|
| Diagnostics (`publishDiagnostics`) | ✅ Done | Fully wired |
| Hover — expression type | ✅ Done | Via `contextScope->typeOf()` |
| Hover — method signature | ✅ Done | `UserlandMethod->targetType/paramType/returnType` |
| Hover — type definition | ✅ Done | Via `userlandTypeRegistry->withName()` |
| Go-to-definition — type refs | ✅ Done | TypeName → NamedType → `codeIndex->getSourceLocation()` |
| Go-to-definition — method calls | ✅ Done | UserlandMethod → `codeIndex->getSourceLocation()` |
| Go-to-definition — variables | ❌ Not supported | Definition-site not tracked in NodeCodeMapper |
| Completion — methods (`->`) | ✅ Done | Userland methods only (native not enumerable) |
| Completion — type names | ✅ Done | Userland + `BUILTIN_TYPES` constant |
| Completion — variables | ✅ Done | `contextScope->scopeAt(functionBody)->variables()` |
| Signature help | ❌ Not implemented | Walnut doesn't use `(` trigger; skipped |
| Find references | ❌ Not supported | Requires full AST visitor infrastructure |
| Rename | ❌ Not supported | Requires find references + source rewrite |

---

## PHP server architecture

```
bin/
  almond-lsp              ← bash wrapper (exec php almond-lsp.php)
  almond-lsp.php          ← PHP stdio entry point

src/Almond/LSP/
  Blueprint/
    Transport/
      LspTransport.php          ← read/write JSON-RPC messages
    Document/
      DocumentStore.php         ← live source per URI
      CompilationCache.php      ← snapshots per URI (live/parsed/valid)
      CompilationSnapshot.php   ← one compiled result + ValidationContextScope
    Feature/
      DiagnosticsProvider.php
      HoverProvider.php
      DefinitionProvider.php
      CompletionProvider.php
      SignatureHelpProvider.php
    Server/
      LspServer.php             ← main loop interface
  Implementation/
    Transport/
      StdioTransport.php        ← fgets(STDIN) / fwrite(STDOUT) framing
    Document/
      InMemoryDocumentStore.php
      TwoLevelCompilationCache.php
      BasicCompilationSnapshot.php
      NoopValidationContextScope.php
    Support/
      LspPositionConverter.php  ← static helpers: offset↔position, location→LSP
    Feature/
      AlmondDiagnosticsProvider.php   ← fully implemented
      AlmondHoverProvider.php         ← fully implemented
      AlmondDefinitionProvider.php    ← fully implemented
      AlmondCompletionProvider.php    ← fully implemented
    Server/
      JsonRpcLspServer.php      ← dispatches to providers; wires BacktraceCollector
```

The LSP source lives under `src/Almond/LSP/` (namespace `Walnut\Lang\Almond\LSP\*`) and is
loaded by the main project autoloader — no separate `composer.json` or `vendor/` needed.

Key design decisions:
- **`CompilationSnapshot` carries `ValidationContextScope`**: after compile, the snapshot
  holds the `BacktraceValidationResultCollector` so feature providers can call
  `typeOf()` and `scopeAt()` per-expression.
- **`CompilationSnapshot.$codeIndex`** typed as `PositionalLocator & SourceNodeLocator`
  intersection — providers can call both `elementsAtOffset()` and `getSourceLocation()`.
- **Sync / single-threaded**: PHP's process model is sync; one request at a time. Fine for local LSP.
- **Recompile on every `didChange`** (client debounces).
- **`withAddedSourceFinder` override**: LSP compiler always prepends `InMemorySourceFinder`
  (live content) before file-based finders.
- **`withValidationResultCollector`**: a fresh `BacktraceValidationResultCollector` is
  created per compile run and stored in the resulting snapshot.
- **Cross-file peer finder**: `buildPeerSourceFinder()` in `JsonRpcLspServer` iterates
  `compilationCache->listUris()`, picks best snapshot (lastValid → lastParsed → live) for
  each peer module, and feeds them into a second `InMemorySourceFinder`.

---

## VS Code client architecture

```
client/
  package.json            ← extension manifest + deps (vscode-languageclient)
  tsconfig.json
  language-configuration.json  ← brackets, comments, word pattern
  src/
    extension.ts          ← activate() spawns PHP server, wires LanguageClient
```

The extension spawns `php /path/to/bin/almond-lsp.php` as a child process and
connects via `StdioServerTransport` from `vscode-languageclient/node`.

The default path (when `walnut.server.path` is not set) is resolved relative to the
extension's own directory: `../../bin/almond-lsp.php` — which points to the project-root
`bin/` when running in development mode from `lsp/client/`.

Language ID: `walnut` (for `.nut` files).

---

## Known limitations

- **Native-only method completions** are not available (NativeMethodRegistry has no iteration API).
- **Variable go-to-definition** not supported (NodeCodeMapper doesn't separately track declaration sites vs. use sites for variables).
- **signatureHelp** not wired (Walnut method calls use `->name` without parentheses, so the `(` trigger character doesn't apply naturally).
- **UTF-16 character offsets**: LSP specifies character positions as UTF-16 code units; `LspPositionConverter` uses byte offsets. This is equivalent for ASCII/single-byte Walnut source; multi-byte identifiers would need proper handling.

---

## Implementation order (completed)

**Almond additions:**
1. ✅ `PositionalLocator` interface + `NodeCodeMapper` positional index (Gap 1)
2. ✅ `ValidationResultCollector` / `ValidationContextScope` + `BacktraceValidationResultCollector` (Gaps 2+3)
3. ⚠️  Method enumeration — partial via `userlandMethodRegistry->allMethods()` (Gap 4)

**PHP server (in `src/Almond/LSP/`, entry point `bin/almond-lsp.php`):**
1. ✅ Transport (stdio JSON-RPC framing) — `StdioTransport`
2. ✅ Document store + compilation cache — `InMemoryDocumentStore`, `TwoLevelCompilationCache`
3. ✅ Snapshot with `ValidationContextScope` — `BasicCompilationSnapshot`
4. ✅ Diagnostics — `AlmondDiagnosticsProvider`
5. ✅ `BacktraceValidationResultCollector` wired in `JsonRpcLspServer::recompileAndPublish()`
6. ✅ Hover — `AlmondHoverProvider` (expression type, method signature, type definition)
7. ✅ Definition — `AlmondDefinitionProvider` (type refs, userland methods)
8. ✅ Completion — `AlmondCompletionProvider` (methods, type names, variables)
9. ✅ `LspPositionConverter` static utility (position↔offset, location→LSP range/location)

**VS Code client (in `lsp/client/`):**
1. ✅ Extension scaffold (package.json, tsconfig, language-configuration.json)
2. ✅ `extension.ts` activation + server spawn
3. Language grammar (`.tmLanguage`) — separate task
