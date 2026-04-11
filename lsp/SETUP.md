# Walnut LSP — Setup & Usage Guide

## Prerequisites

| Tool | Version | Why |
|------|---------|-----|
| PHP | ≥ 8.5 | Runs the language server |
| Composer | any | Installs PHP dependencies |
| Node.js / npm | ≥ 18 | Builds the VS Code extension |
| VS Code | ≥ 1.85 | The target editor |

---

## 1. Install server dependencies

The PHP server lives in `lsp/server/` and depends on the main `walnut/lang-walnut` package
via a local Composer path repository.

```bash
cd lsp/server
composer install
```

This produces `lsp/server/vendor/` with the autoloader wired up.
The server entry point is `lsp/server/bin/walnut-lsp`.

---

## 2. Install client dependencies

The VS Code extension lives in `lsp/client/` and is written in TypeScript.

```bash
cd lsp/client
npm install
```

---

## 3. Quick smoke-test (no VS Code needed)

You can verify the server starts and responds correctly by piping a raw JSON-RPC
`initialize` request to it:

```bash
cd lsp/server

# Build the JSON payload
MSG='{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"rootUri":null,"capabilities":{}}}'
LEN=${#MSG}

# Send: Content-Length header + blank line + body
printf "Content-Length: %d\r\n\r\n%s" "$LEN" "$MSG" | php bin/walnut-lsp
```

You should see a JSON-RPC response that begins with `Content-Length:` followed by a
JSON object containing `"capabilities"`.  The server then waits for more input; press
`Ctrl-C` to exit.

### Longer smoke-test (open a file and check diagnostics)

```bash
cd lsp/server

send() {
  local msg="$1"
  printf "Content-Length: %d\r\n\r\n%s" "${#msg}" "$msg"
}

{
  send '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"rootUri":null,"capabilities":{}}}'
  send '{"jsonrpc":"2.0","method":"initialized","params":{}}'
  send '{"jsonrpc":"2.0","method":"textDocument/didOpen","params":{"textDocument":{"uri":"file:///tmp/walnut-src/test.nut","languageId":"walnut","version":1,"text":"module test:\n"}}}'
  # Give the server a moment to compile and reply, then shut down cleanly
  send '{"jsonrpc":"2.0","id":2,"method":"shutdown","params":{}}'
  send '{"jsonrpc":"2.0","method":"exit","params":{}}'
} | php bin/walnut-lsp
```

You should see the `initialize` response, then a `textDocument/publishDiagnostics`
notification (empty array if the module compiled cleanly), then the `shutdown` response.

---

## 4. Run inside VS Code — development mode (no install required)

This is the fastest way to try the extension without packaging it.

### 4a. Add a launch configuration

A `.vscode/launch.json` file is provided in `lsp/client/`. Open the `lsp/client/`
folder as your VS Code workspace (or add it to a multi-root workspace), then:

1. Compile the TypeScript once:
   ```bash
   cd lsp/client
   npm run compile
   # or keep it watching during development:
   npm run watch
   ```

2. Press **F5** (or use *Run → Start Debugging*).

VS Code launches an **Extension Development Host** window with the extension active.
Open any `.nut` file — you should see:
- Diagnostics (red squiggles) on type errors
- Hover to see inferred types and method signatures
- Go-to-definition on type names and method calls
- Completion after typing `->` or in type-annotation positions

### 4b. Server path resolution

By default the extension resolves the server relative to its own directory:
```
lsp/client/../server/bin/walnut-lsp
```

If you need to override this (e.g. server is installed elsewhere):
1. Open VS Code settings (`Cmd+,` on macOS)
2. Search for `walnut`
3. Set **Walnut › Server: Path** to the absolute path of `walnut-lsp`
4. Optionally set **Walnut › Server: Php Path** if `php` is not on your `$PATH`

---

## 5. Install as a VSIX package

To install the extension permanently (without cloning the repo):

```bash
cd lsp/client

# Install the vsce packaging tool globally if you don't have it
npm install -g @vscode/vsce

# Compile TypeScript
npm run compile

# Package into a .vsix
vsce package
# → produces walnut-language-support-0.1.0.vsix

# Install into VS Code
code --install-extension walnut-language-support-0.1.0.vsix
```

After installation, restart VS Code and open a `.nut` file.

> **Note**: The VSIX bundles only the compiled `out/` JS and the `language-configuration.json`.
> The PHP server (`lsp/server/`) must still be installed separately (step 1) and the
> `walnut.server.path` setting must point to `bin/walnut-lsp` if the server is not
> co-located with the extension directory.

---

## 6. Viewing server logs

The extension creates an output channel called **"Walnut Language Server"**.

In VS Code: *View → Output* → select **Walnut Language Server** from the dropdown.

All JSON-RPC traffic and PHP stderr output appears there.

---

## 7. Troubleshooting

### First: check the Output channel

In VS Code: *View → Output* → select **"Walnut Language Server"** from the dropdown.
Every error the PHP server writes to stderr, and every JSON-RPC failure, appears there.
This is always the first place to look.

### Verify / clear the server path setting

If VS Code is trying to launch an old/moved server:

1. *File → Preferences → Settings*, search `walnut`
2. Check **Walnut › Server: Path** — if it shows an old path, **clear it completely**
3. `Cmd+Shift+P` → **Developer: Reload Window**

When the path setting is empty the extension resolves the server relative to its own
install directory (`../server/bin/walnut-lsp`), which is the correct default for the
development layout in this repo.

### Common errors

| Symptom | Fix |
|---------|-----|
| `vendor/autoload.php: No such file or directory` | Run `composer install` in `lsp/server/` |
| `Class ... not found` | Check `composer install` completed without errors; verify `walnut/lang` is in `composer.lock` |
| Server process exits immediately | PHP version must be ≥ 8.5 — run `php --version` to confirm |
| No server output at all | Check `walnut.server.phpPath` — is `php` on your `$PATH`? Try setting it to the absolute path |
| Extension never activates | File must have `.nut` extension (not `.nut.html` or `.walnut`) |
| Hover shows nothing | Parse errors prevent type analysis; fix red squiggles first, then hover will work |
| Stale behaviour after code change | `npm run compile` in `lsp/client/`, then *Developer: Reload Window* |
