
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

