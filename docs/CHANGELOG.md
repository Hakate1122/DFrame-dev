
## `2026.4.12-dev` (2026-04-12)

### Fixed

- **WebSocket** (`src/Application/WebSocket.php`): `triggerEvent` now invokes overridden `onOpen` / `onMessage` / `onClose` methods when the public callback properties are `null`. In PHP those properties shadow same-named methods, so subclasses such as `App\Chat\Chat` previously never received events after a successful handshake (messages appeared to “send” from the browser but were not handled server-side).
- **WebSocket**: frame reads use `readBytes()` so short TCP reads cannot corrupt parsing or disconnect clients unnecessarily.
- **WebSocket**: `disconnect()` sends an RFC 6455 **Close** frame (unmasked server → client) before `socket_close`, completing the closing handshake when the client calls `close()` (e.g. Logout). This avoids abnormal closure **1006** / `wasClean: false` in typical browsers.
- **WebSocket**: `disconnect()` calls `triggerEvent('onClose', …)` so a callable set on the `$onClose` property is honored like the other events.

## `2026.4.11-dev` (2026-04-11)

### Added

- `php dli help:add` prints detailed usage for `add`, `add:<type>`, supported type aliases, options (`--name`, `-n`), and per-generator output paths (controller CRUD and nested paths, model/view/command/middleware/mail behavior).
- `php dli add:model` accepts **`--table=`** and **`--selectable=`** (comma list or `[col1,col2]`). If `--table` is omitted, the table name defaults to snake_case derived from the class name (e.g. `Posts` → `posts`).

### Changed

- `add:model` no longer forces a **`Model` class-name suffix** or `SomethingModel.php` naming; the generated file is `app/Model/{Class}.php` matching `Users` / `Products` style. Success output is a single path (no misleading `->` style message).

## `2026.4.8-dev` (2026-04-08)

### Added

- `dli version` can optionally display **GitHub/Git source info** (origin remote URL, current branch, short commit hash) when running inside a directory that contains a `.git/` folder.  
  The command asks the user before printing these details to keep default output clean.

### Changed

- Running DFrame from a **PHAR** no longer hard-requires a `.env` file to exist next to / inside the PHAR.  
  When running from PHAR, environment loading now also checks the **current working directory** (where you execute the command), and will continue without `.env` if none is found (so CLI/report/help can run).

