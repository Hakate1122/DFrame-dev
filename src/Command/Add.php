<?php

namespace DFrame\Command;

class Add
{
	/**
	 * Generic add handler for `php dli add <type> --name=Name`
	 */
	public static function handle()
	{
		return function ($argv = []) {
			$type = $argv[2] ?? null;
			if (!$type) {
				echo "Usage: php dli add <type> --name=Name\n";
				echo "Example: php dli add controller --name=MyController\n";
				return;
			}

			$type = strtolower($type);
				switch ($type) {
					case 'controller':
					case 'ctrl':
						(self::controller())($argv);
						break;
					case 'model':
						(self::model())($argv);
						break;
					case 'view':
						(self::view())($argv);
						break;
					case 'command':
					case 'cmd':
						(self::command())($argv);
						break;
					case 'middleware':
					case 'mdw':
						(self::middleware())($argv);
						break;
					case 'mail':
						(self::mail())($argv);
						break;
					default:
						echo "Unknown add type: $type\n";
				}
		};
	}

	/**
	 * Handler for `add:controller` or `add controller`
	 */
	public static function controller()
	{
		return function ($argv = []) {
			$start = ($argv[1] === 'add') ? 3 : 2;
			$opts = self::parseOptions($argv, $start);

			$name = $opts['name'] ?? $opts['n'] ?? null;
			if (!$name) {
				echo "Please provide a name: --name=MyController\n";
				return;
			}

			$name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
			if (!str_ends_with($name, 'Controller')) {
				$name .= 'Controller';
			}

			$dir = __DIR__ . '/../../app/Controller';
			if (!is_dir($dir)) {
				if (!@mkdir($dir, 0755, true)) {
					echo "Failed to create directory: $dir\n";
					return;
				}
			}

			$file = $dir . '/' . $name . '.php';
			if (file_exists($file)) {
				echo "Controller already exists: app/Controller/$name.php\n";
				return;
			}

			$template = "<?php\n\nnamespace App\\Controller;\n\nclass $name\n{\n    public function index()\n    {\n        echo \"This is $name\";\n    }\n}\n";

			if (file_put_contents($file, $template) !== false) {
				echo "Created controller: app/Controller/$name.php\n";
			} else {
				echo "Failed to create controller: $file\n";
			}
		};
	}

	public static function model()
	{
		return function ($argv = []) {
			$start = ($argv[1] === 'add') ? 3 : 2;
			$opts = self::parseOptions($argv, $start);

			$name = $opts['name'] ?? $opts['n'] ?? null;
			if (!$name) {
				echo "Please provide a name: --name=MyModel\n";
				return;
			}

			$name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
			if (!str_ends_with($name, 'Model')) {
				$name .= 'Model';
			}

			$dir = __DIR__ . '/../../app/Model';
			if (!is_dir($dir)) {
				if (!@mkdir($dir, 0755, true)) {
					echo "Failed to create directory: $dir\n";
					return;
				}
			}

			$file = $dir . '/' . $name . '.php';
			if (file_exists($file)) {
				echo "Model already exists: app/Model/$name.php\n";
				return;
			}

			$template = "<?php\n\nnamespace App\\Model;\n\nuse App\\Model\\Model;\n\nclass $name extends Model\n{\n    protected \$table = '';\n}\n";

			if (file_put_contents($file, $template) !== false) {
				echo "Created model: app/Model/$name.php\n";
			} else {
				echo "Failed to create model: $file\n";
			}
		};
	}

	public static function view()
	{
		return function ($argv = []) {
			$start = ($argv[1] === 'add') ? 3 : 2;
			$opts = self::parseOptions($argv, $start);

			$name = $opts['name'] ?? $opts['n'] ?? null;
			if (!$name) {
				echo "Please provide a name: --name=templateName\n";
				return;
			}

			$name = preg_replace('/[^A-Za-z0-9_\.\-]/', '', $name);
			$dir = __DIR__ . '/../../resource/view';
			if (!is_dir($dir)) {
				if (!@mkdir($dir, 0755, true)) {
					echo "Failed to create directory: $dir\n";
					return;
				}
			}

			$file = $dir . '/' . $name . '.php';
			if (file_exists($file)) {
				echo "View already exists: resource/view/$name.php\n";
				return;
			}

			$template = "<?php\n\n/** View: $name */\n?>\n<h1>$name</h1>\n";

			if (file_put_contents($file, $template) !== false) {
				echo "Created view: resource/view/$name.php\n";
			} else {
				echo "Failed to create view: $file\n";
			}
		};
	}

	public static function command()
	{
		return function ($argv = []) {
			$start = ($argv[1] === 'add') ? 3 : 2;
			$opts = self::parseOptions($argv, $start);

			$name = $opts['name'] ?? $opts['n'] ?? null;
			if (!$name) {
				echo "Please provide a name: --name=MyCommand\n";
				return;
			}

			$name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
			if (!str_ends_with($name, 'Command')) {
				$name .= 'Command';
			}

			$dir = __DIR__ . '/..'; // src/Command
			if (!is_dir($dir)) {
				if (!@mkdir($dir, 0755, true)) {
					echo "Failed to create directory: $dir\n";
					return;
				}
			}

			$file = $dir . '/' . $name . '.php';
			if (file_exists($file)) {
				echo "Command already exists: src/Command/$name.php\n";
				return;
			}

			$template = "<?php\n\nnamespace DFrame\\Command;\n\nclass $name\n{\n    public static function handle()\n    {\n        return function (\$argv = []) {\n            echo \"$name executed\";\n        };\n    }\n}\n";

			if (file_put_contents($file, $template) !== false) {
				echo "Created command: src/Command/$name.php\n";
			} else {
				echo "Failed to create command: $file\n";
			}
		};
	}

	public static function middleware()
	{
		return function ($argv = []) {
			$start = ($argv[1] === 'add') ? 3 : 2;
			$opts = self::parseOptions($argv, $start);

			$name = $opts['name'] ?? $opts['n'] ?? null;
			if (!$name) {
				echo "Please provide a name: --name=MyMiddleware\n";
				return;
			}

			$name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
			if (!str_ends_with($name, 'Middleware')) {
				$name .= 'Middleware';
			}

			$dir = __DIR__ . '/../../app/Middleware';
			if (!is_dir($dir)) {
				if (!@mkdir($dir, 0755, true)) {
					echo "Failed to create directory: $dir\n";
					return;
				}
			}

			$file = $dir . '/' . $name . '.php';
			if (file_exists($file)) {
				echo "Middleware already exists: app/Middleware/$name.php\n";
				return;
			}

			$template = "<?php\n\nnamespace App\\Middleware;\n\nuse DFrame\\Application\\Middleware;\n\nclass $name extends Middleware\n{\n    public static function sign(): void\n    {\n        Middleware::register('{$name}', function () {\n            // TODO: implement middleware logic\n            return null;\n        });\n    }\n}\n";

			if (file_put_contents($file, $template) !== false) {
				echo "Created middleware: app/Middleware/$name.php\n";
			} else {
				echo "Failed to create middleware: $file\n";
			}
		};
	}

	public static function mail()
	{
		return function ($argv = []) {
			$start = ($argv[1] === 'add') ? 3 : 2;
			$opts = self::parseOptions($argv, $start);

			$name = $opts['name'] ?? $opts['n'] ?? null;
			if (!$name) {
				echo "Please provide a name: --name=MyMailer\n";
				return;
			}

			$name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
			if (!str_ends_with($name, 'Mail') && !str_ends_with($name, 'Mailer')) {
				$name .= 'Mail';
			}

			$dir = __DIR__ . '/../../app/Mail';
			if (!is_dir($dir)) {
				if (!@mkdir($dir, 0755, true)) {
					echo "Failed to create directory: $dir\n";
					return;
				}
			}

			$file = $dir . '/' . $name . '.php';
			if (file_exists($file)) {
				echo "Mail class already exists: app/Mail/$name.php\n";
				return;
			}

			$template = "<?php\n\nnamespace App\\Mail;\n\nclass $name\n{\n    public function send(\$to, \$subject, \$body)\n    {\n        // integrate with Mailer\n        return true;\n    }\n}\n";

			if (file_put_contents($file, $template) !== false) {
				echo "Created mail class: app/Mail/$name.php\n";
			} else {
				echo "Failed to create mail class: $file\n";
			}
		};
	}

	private static function parseOptions(array $argv, int $start = 2): array
	{
		$opts = [];
		for ($i = $start; $i < count($argv); $i++) {
			$arg = $argv[$i];
			if (str_starts_with($arg, '--')) {
				$parts = explode('=', substr($arg, 2), 2);
				$opts[$parts[0]] = $parts[1] ?? true;
			} elseif (str_starts_with($arg, '-')) {
				$key = ltrim($arg, '-');
				$val = $argv[$i + 1] ?? true;
				if (!str_starts_with((string)$val, '-')) {
					$opts[$key] = $val;
					$i++;
				} else {
					$opts[$key] = true;
				}
			}
		}
		return $opts;
	}

}