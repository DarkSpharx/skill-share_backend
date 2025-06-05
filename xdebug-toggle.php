<?php

// toggle-xdebug.php

function logMessage(string $message): void
{
    file_put_contents("xdebug_toggle.log", date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

function getPhpIniPath(): string
{
    return php_ini_loaded_file() ?: 'Unknown';
}

function readIni(string $path): string
{
    if (!file_exists($path)) {
        fwrite(STDERR, "INI file not found at $path\n");
        exit(1);
    }
    return file_get_contents($path);
}

function writeIni(string $path, string $content): void
{
    backupIni($path);
    file_put_contents($path, $content);
}

function backupIni(string $path): void
{
    copy($path, $path . '.bak');
}

function restoreIni(string $path): void
{
    $backup = $path . '.bak';
    if (!file_exists($backup)) {
        echo "No backup found.\n";
        return;
    }
    copy($backup, $path);
    echo "Backup restored.\n";
}

function toggleXdebug(string $path, bool $enable, bool $dryRun = false): void
{
    $ini = readIni($path);
    $pattern = '/(;)?(zend_extension\s*=.*php_xdebug[^\r\n]*)/i';
    $replacement = $enable ? '$2' : ';$2';
    $newIni = preg_replace($pattern, $replacement, $ini);

    if ($ini === $newIni) {
        echo $enable ? "Xdebug already enabled.\n" : "Xdebug already disabled.\n";
        return;
    }

    if ($dryRun) {
        echo "--- Dry Run ---\n$newIni\n";
    } else {
        writeIni($path, $newIni);
        echo $enable ? "Xdebug enabled.\n" : "Xdebug disabled.\n";
        logMessage(($enable ? 'Enabled' : 'Disabled') . " Xdebug in $path");
    }
}

function showStatus(string $path): void
{
    $ini = readIni($path);
    if (preg_match('/^zend_extension\s*=.*php_xdebug/m', $ini)) {
        echo "Xdebug is ACTIVE\n";
    } else if (preg_match('/^;zend_extension\s*=.*php_xdebug/m', $ini)) {
        echo "Xdebug is INACTIVE\n";
    } else {
        echo "Xdebug not found in ini.\n";
    }
}

function showExtensions(): void
{
    $exts = get_loaded_extensions();
    echo "Loaded extensions (" . count($exts) . "):\n";
    echo implode(", ", $exts) . "\n";
}

function setIniOption(string $path, string $key, string $value): void
{
    $ini = readIni($path);
    $pattern = "/^\s*{$key}\s*=.*$/m";
    $replacement = "$key = $value";

    if (preg_match($pattern, $ini)) {
        $newIni = preg_replace($pattern, $replacement, $ini);
    } else {
        $newIni = $ini . PHP_EOL . $replacement;
    }

    writeIni($path, $newIni);
    echo "$key set to $value.\n";
    logMessage("Set $key to $value in $path");
}

function showHelp(): void
{
    echo <<<HELP
Usage:
  php toggle-xdebug.php [--enable|--disable|--status|--restore|--version|--dry-run|--list|--set key=value|--path=path]

Options:
  --enable           Enable Xdebug
  --disable          Disable Xdebug
  --status           Show current Xdebug status
  --restore          Restore php.ini from backup
  --version          Show PHP version and ini path
  --dry-run          Preview changes without saving
  --list             List active PHP extensions
  --set key=value    Set a php.ini option
  --path=path        Path to php.ini (optional, autodetected if not provided)
  --help             Show this help
HELP;
}

// Parse arguments
$args = getopt("", ["enable", "disable", "status", "restore", "version", "dry-run", "list", "set:", "path:", "help"]);

if (isset($args['help']) || empty($args)) {
    showHelp();
    exit;
}

$path = $args['path'] ?? getPhpIniPath();

if (isset($args['version'])) {
    echo "PHP Version: " . phpversion() . "\n";
    echo "php.ini: $path\n";
    exit;
}

if (isset($args['restore'])) {
    restoreIni($path);
    exit;
}

if (isset($args['status'])) {
    showStatus($path);
    exit;
}

if (isset($args['list'])) {
    showExtensions();
    exit;
}

if (isset($args['set'])) {
    [$key, $val] = explode("=", $args['set'], 2);
    setIniOption($path, trim($key), trim($val));
    exit;
}

$dryRun = isset($args['dry-run']);
if (isset($args['enable'])) {
    toggleXdebug($path, true, $dryRun);
    exit;
} elseif (isset($args['disable'])) {
    toggleXdebug($path, false, $dryRun);
    exit;
}

showHelp();
