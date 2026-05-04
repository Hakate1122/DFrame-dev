## `2026.5.4-dev` (2026-05-04)

### Changed
- Update function `dd()` / `dump()` output formatting and enhance `craft_custom_var_dump()` with CLI ANSI colors and HTML color spans.

## `2026.5.3-dev` (2026-05-03)

### Added
- Add Docker runtime detection in `src/Application/App.php` via `App::isRunningFromDocker()` with checks for `DOCKER_RUNNING`, `/.dockerenv`, and container cgroup markers. This allows the framework and CLI to adapt behavior when running inside Docker.
- Add Docker runtime notice in `src/Command/Core.php` help output, so `dli` now explicitly informs users when commands are executed inside a container.

### Changed
- Update `docker-compose.yml` to set `DOCKER_RUNNING=true` for the `dframe-app` service, providing an explicit runtime flag that complements filesystem/cgroup detection in containerized environments.
- Class `Mail` now supports multiple SMTP providers via `SERVICE_PRESETS` constant, allowing easy configuration of different mail services (e.g. Gmail, Outlook, Yahoo, etc.).

## `2026.5.2-dev` (2026-05-02)

### Added
- Add `CallWrongMethodOnDbDesign` runtime guard for dynamic model/database proxy calls. When a method is called on the wrong DB design (`mapper` vs `builder`), DFrame now throws a clear domain exception instead of PHP callback errors from `call_user_func_array`.
- Add explicit design-switching APIs in `src/Application/DB.php`: `DB::mapper()` to force mapper mode and `DB->builder()` to switch the current instance to builder mode without changing `.env` `DB_DESIGN`.

### Changed
- Update dynamic dispatch in `app/Model/Model.php` and `src/Application/DB.php` to validate method existence before forwarding calls to mapper/builder instances. This improves DX by reporting design-layer mismatches with actionable error messages (for example, calling `all()` while using `builder` design).
- Refactor `src/Database/DatabaseManager.php` to support runtime design switching via `switchDesign()` and centralized class resolution via `resolveMapperClass()`, so mapper/builder selection works consistently for both MySQL and SQLite drivers.

## `2026.5.1-dev` (2026-05-01)

### Added
- Add `rectorPHP` for automated refactoring and code quality improvements. Initial configuration includes rules for modernizing PHP syntax, improving readability, and enforcing best practices. This will help maintain a clean and efficient codebase as the project evolves.
- Add Docker configuration for development and testing environments. This includes a `Dockerfile` for the application, a `docker-compose.yml` to orchestrate services (PHP-FPM, Nginx, MySQL), and an Nginx configuration file. This setup allows developers to quickly spin up a consistent environment for development and testing without worrying about local dependencies or configurations.

### Changed
- Refactor `src/Command/Register.php` to use `registerAlias` for command aliases (`-h` → `help`, `-v` → `version`, `-s` → `server`, `make` → `add`) instead of separate registrations. This reduces redundancy and centralizes alias management, making it easier to maintain and extend command aliases in the future.
- Update `src/Command/Add.php` to improve option parsing and help display. The command now checks for options like `--help` or `-h` to display usage information, and it uses a more robust method for parsing options from the command line arguments. This enhances the user experience by providing clearer guidance on how to use the `add` command and its subcommands.

Before v2026.5.1:
```bash
php dli help:add # shows help for add command and its only
```

After v2026.5.1, you can use either of the following to show help for the add command:
```bash
php dli add
php dli add --help
php dli add -h
```

## `2026.4.13-dev` (2026-04-13)

### Changed

- **Helper namespace/layout refactor**: moved all helpers from `src/Application/helper/*` to `src/Helper/*` to group shared modules together.
- **Remove `src/Kit`**: Kit utility functions have been consolidated into `src/Helper/functions.php`, reducing helper scattering and simplifying import points.

## `2026.4.12-dev` (2026-04-12)

### Added

- **CLI `add` scaffolds** (`src/Command/Add.php`): **`--force`** on every generator (`controller`, `model`, `view`, `command`, `middleware`, `mail`) overwrites the target file when it already exists; success message uses “Overwrote” instead of “Created”.
- **`add:controller` / `add controller`**: **`--api-crud`** generates REST-style JSON stubs (`index`, `store`, `show`, `update`, `destroy`) with `Content-Type: application/json` and sample `json_encode` responses. If both `--crud` and `--api-crud` are passed, **`--api-crud` wins**.
- **`php dli help:add`** documents `--force`, `--api-crud`, and updated examples (`src/Command/Core.php`).

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

