import * as path from 'path';
import * as vscode from 'vscode';
import {
    LanguageClient,
    LanguageClientOptions,
    ServerOptions,
    TransportKind,
} from 'vscode-languageclient/node';

let client: LanguageClient | undefined;

/** How long to wait after the last keystroke before sending didChange to the server. */
const DEBOUNCE_MS = 300;

/** Flush the pending didChange notification to the server immediately (once). */
let pendingFlush: (() => Promise<void>) | undefined;
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

async function flushNow(): Promise<void> {
    if (!pendingFlush) { return; }
    clearTimeout(debounceTimer);
    const fn = pendingFlush;
    pendingFlush = undefined;   // clear BEFORE awaiting to prevent double-send
    debounceTimer = undefined;
    await fn();
}

export function activate(context: vscode.ExtensionContext): void {
    const config = vscode.workspace.getConfiguration('walnut');

    // Resolve the PHP binary
    const phpPath: string = config.get('server.phpPath') || 'php';

    // Resolve the server entry point
    const customServerPath: string = config.get('server.path') || '';
    const serverScript = customServerPath || context.asAbsolutePath(
        path.join('..', '..', 'bin', 'almond-lsp.php')
    );

    // Server is launched as a child process, communicating via stdio
    const serverOptions: ServerOptions = {
        run: {
            command: phpPath,
            args: [serverScript],
            transport: TransportKind.stdio,
        },
        debug: {
            command: phpPath,
            args: [serverScript, '--debug'],
            transport: TransportKind.stdio,
        },
    };

    // What files trigger the extension
    const clientOptions: LanguageClientOptions = {
        documentSelector: [
            { scheme: 'file', language: 'walnut' },
        ],
        synchronize: {
            // Watch nutcfg.json for changes so the server can reload its config
            fileEvents: vscode.workspace.createFileSystemWatcher('**/nutcfg.json'),
        },
        outputChannelName: 'Walnut Language Server',
        middleware: {
            // Debounce didChange: only send to the server after DEBOUNCE_MS of
            // typing silence, to avoid a compilation per keystroke.
            // The flush callback is saved so completion can force an early send.
            didChange: async (event, next) => {
                clearTimeout(debounceTimer);
                pendingFlush = () => next(event);
                debounceTimer = setTimeout(() => flushNow(), DEBOUNCE_MS);
            },

            // When '->' triggers completion, the debounced didChange hasn't reached
            // the server yet — flush it NOW (before the completion request) so the
            // server's documentStore contains the current source text.
            provideCompletionItem: async (document, position, context, token, next) => {
                if (context.triggerCharacter === '>') {
                    await flushNow();
                }
                return next(document, position, context, token);
            },
        },
    };

    client = new LanguageClient(
        'walnut-lsp',
        'Walnut Language Server',
        serverOptions,
        clientOptions
    );

    // Start the client (and the underlying PHP server process)
    client.start();

    context.subscriptions.push({
        dispose: () => client?.stop(),
    });
}

export function deactivate(): Thenable<void> | undefined {
    return client?.stop();
}
