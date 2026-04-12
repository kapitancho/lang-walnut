<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Server;

/**
 * Top-level server loop.
 *
 * Reads JSON-RPC messages from the transport, dispatches them to the
 * appropriate handlers, and writes responses back.
 */
interface LspServer {

    /**
     * Enter the main event loop. Blocks until the client sends
     * a shutdown request followed by an exit notification.
     */
    public function run(): void;
}
