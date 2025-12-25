<?php

namespace DFrame\Command;

/**
 * A command to create and start a Vite development server.
 * 
 * Usage:
 *   php dli vite
 * 
 * This command will:
 * 1) Check for Node.js and npm installation.
 * 2) Prompt the user to select a JavaScript framework (Vanilla, Vue, React, Angular).
 * 3) Create a Vite project scaffold in 'vite_project/' if it doesn't exist.
 * 4) Install npm dependencies.
 * 5) Start the Vite development server.
 */
class Vite
{
    public static function create()
    {
        return function () {
            $root = defined('ROOT_DIR') ? ROOT_DIR : throw new \Exception('ROOT_DIR is not defined');

            $viteRoot   = $root . '/vite_project';
            $vitePublic = $viteRoot . '/public';

            // 1) CHECK NODE + NPM
            if (!self::checkNode()) {
                echo cli_red("Node.js and npm are required to run the Vite development server.\n");
                return;
            }

            // 2) ASK USER WHICH TEMPLATE THEY WANT
            $template = self::askTemplate();
            echo "➡ Selected template: $template\n";

            // 3) CREATE PROJECT IF NOT EXISTS
            if (!file_exists($viteRoot . '/package.json')) {
                self::createScaffold($viteRoot, $vitePublic, $template);

                echo "📦 Installing npm packages...\n";
                $cwd = getcwd();
                chdir($viteRoot);
                passthru('npm install', $exit);
                chdir($cwd);

                if ($exit !== 0) {
                    echo cli_red("npm install failed. Please check the output above.\n");
                    return;
                }
            } else {
                echo cli_green("Vite project already exists. Skipping scaffold creation.\n");
            }

            // 4) START DEV SERVER
            echo "🚀 Starting Vite dev server (http://localhost:5173)\n";
            $cwd = getcwd();
            chdir($viteRoot);
            passthru('npm run dev', $devExit);
            chdir($cwd);

            if ($devExit !== 0) {
                echo cli_red("Vite dev server exited with errors.\n");
            }
        };
    }

    /* ============================================================
     * CHECK NODE & NPM
     * ============================================================ */
    private static function checkNode(): bool
    {
        exec('node -v', $o1, $nodeExit);
        exec('npm -v', $o2, $npmExit);

        return $nodeExit === 0 && $npmExit === 0;
    }

    /* ============================================================
     * ASK USER WHICH TEMPLATE
     * ============================================================ */
    private static function askTemplate(): string
    {
        echo "Choose JS framework:\n";
        echo "  [1] Vanilla (no framework)\n";
        echo "  [2] Vue\n";
        echo "  [3] React\n";
        echo "  [4] Angular\n";
        echo "Your choice (1-4): ";

        $choice = trim(fgets(STDIN));

        return match ($choice) {
            '2' => 'vue',
            '3' => 'react',
            '4' => 'angular',
            default => 'vanilla'
        };
    }

    /* ============================================================
     * CREATE PROJECT SCAFFOLD
     * ============================================================ */
    private static function createScaffold(string $viteRoot, string $vitePublic, string $template)
    {
        echo "📁 Creating scaffold in vite_project/ ...\n";

        self::safeMkdir($viteRoot);
        self::safeMkdir($vitePublic);
        self::safeMkdir($viteRoot . '/src');

        // package.json
        $pkg = [
            "name" => "dframe-vite",
            "private" => true,
            "scripts" => [
                "dev"     => "vite",
                "build"   => "vite build",
                "preview" => "vite preview --port 5173"
            ],
            "devDependencies" => self::templateDependencies($template),
        ];
        file_put_contents($viteRoot . '/package.json', json_encode($pkg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // public index.html
        file_put_contents($vitePublic . '/index.html', self::templateIndexHtml($template));

        // main.js / main.jsx / etc
        file_put_contents($viteRoot . '/src/' . self::templateEntryName($template), self::templateEntryContent($template));

        // style
        file_put_contents($vitePublic . '/style.css', "body{font-family:Arial,Helvetica,sans-serif;padding:20px}\n");

        // vite.config.js
        file_put_contents($viteRoot . '/vite.config.js', self::viteConfig());
    }

    /* --- HELPER METHODS --- */
    private static function safeMkdir(string $dir)
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException("Cannot create directory: $dir");
        }
    }

    /* --- TEMPLATE CONFIGS --- */
    private static function templateDependencies(string $tpl): array
    {
        return match ($tpl) {
            'vue' => [
                "vite" => "^5.0.0",
                "vue" => "^3.4.0",
                "@vitejs/plugin-vue" => "^5.0.0"
            ],
            'react' => [
                "vite" => "^5.0.0",
                "react" => "^18.0.0",
                "react-dom" => "^18.0.0",
                "@vitejs/plugin-react" => "^4.0.0"
            ],
            'angular' => [
                "vite" => "^5.0.0",
                "@analogjs/vite-plugin-angular" => "^1.0.0"
            ],
            default => [
                "vite" => "^5.0.0"
            ]
        };
    }

    private static function templateEntryName(string $tpl): string
    {
        return match ($tpl) {
            'react' => "main.jsx",
            'angular' => "main.ts",
            default => "main.js"
        };
    }

    private static function templateIndexHtml(string $tpl): string
    {
        $entry = $GLOBALS['entry'] ?? self::templateEntryName($tpl);

        return <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Vite + {$tpl}</title>
</head>
<body>
    <div id="app"></div>
    <script type="module" src="/src/{$entry}"></script>
</body>
</html>
HTML;
    }

    private static function templateEntryContent(string $tpl): string
    {
        return match ($tpl) {
            'vue' => <<<JS
import { createApp } from 'vue';
import '../public/style.css';

createApp({
    template: '<h1>Hello from Vue + Vite!</h1>'
}).mount('#app');
JS,

            'react' => <<<JSX
import React from 'react';
import ReactDOM from 'react-dom/client';
import '../public/style.css';

ReactDOM.createRoot(document.getElementById('app')).render(
    <h1>Hello from React + Vite!</h1>
);
JSX,

            'angular' => <<<TS
import '../public/style.css';
console.log("Angular setup placeholder — integrate Angular bootstrap here.");
document.getElementById('app').innerHTML = '<h1>Angular + Vite (placeholder)</h1>';
TS,

            default => <<<JS
import '../public/style.css';
document.getElementById('app').innerHTML = '<h1>Hello from Vanilla JS + Vite!</h1>';
JS
        };
    }

    private static function viteConfig(): string
    {
        return <<<JS
import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  publicDir: 'public',
  server: { open: false },
  build: {
    outDir: '../public/dist',
    emptyOutDir: true
  }
});
JS;
    }
}
