# Changelog

All notable changes to this project will be documented in this file.

## `2026.4.8-dev` (2026-04-08)

### Added

- `dli version` can optionally display **GitHub/Git source info** (origin remote URL, current branch, short commit hash) when running inside a directory that contains a `.git/` folder.  
  The command asks the user before printing these details to keep default output clean.
- `dli tikitiki`: an interactive starter bot to initialize a new DFrame project folder (copy starter template, generate `.env`, optional `vendor/` copy).

### Changed

- Running DFrame from a **PHAR** no longer hard-requires a `.env` file to exist next to / inside the PHAR.  
  When running from PHAR, environment loading now also checks the **current working directory** (where you execute the command), and will continue without `.env` if none is found (so CLI/report/help can run).

