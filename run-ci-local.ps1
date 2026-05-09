# Run moodle-plugin-ci locally (Windows / PowerShell).
# Prereqs: PHP 8.1+ in PATH, composer, git, node 20+, MariaDB/Postgres running.
# Usage:   .\run-ci-local.ps1 [-MoodleBranch MOODLE_500_STABLE] [-Db mariadb|pgsql]

param(
    [string]$MoodleBranch = "MOODLE_500_STABLE",
    [string]$Db = "mariadb",
    [string]$DbHost = "127.0.0.1",
    [string]$DbUser = "root",
    [string]$DbPass = ""
)

$ErrorActionPreference = "Stop"
$PluginDir = $PSScriptRoot
$WorkDir   = Join-Path $env:TEMP "mvp-ci"

Write-Host "=== Plugin dir: $PluginDir"
Write-Host "=== Work  dir: $WorkDir"

if (-not (Test-Path $WorkDir)) { New-Item -ItemType Directory -Path $WorkDir | Out-Null }
Set-Location $WorkDir

if (-not (Test-Path "$WorkDir/ci")) {
    composer create-project -n --no-dev --prefer-dist moodlehq/moodle-plugin-ci ci "^4.5"
}

$env:PATH = "$WorkDir\ci\bin;$WorkDir\ci\vendor\bin;$env:PATH"
$env:DB             = $Db
$env:MOODLE_BRANCH  = $MoodleBranch
$env:DB_USER        = $DbUser
$env:DB_PASS        = $DbPass
$env:DB_HOST        = $DbHost
$env:IGNORE_PATHS   = "vendor,node_modules"

Write-Host "=== moodle-plugin-ci install"
moodle-plugin-ci install --plugin $PluginDir --db-host=$DbHost
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$steps = @(
    @("phplint",      @($PluginDir)),
    @("phpmd",        @($PluginDir)),
    @("codechecker",  @("--max-warnings", "0", $PluginDir)),
    @("phpdoc",       @($PluginDir)),
    @("validate",     @($PluginDir)),
    @("savepoints",   @($PluginDir)),
    @("mustache",     @($PluginDir)),
    @("phpunit",      @("--fail-on-warning", $PluginDir))
)

$failed = @()
foreach ($s in $steps) {
    $name = $s[0]; $args = $s[1]
    Write-Host "`n=== moodle-plugin-ci $name $($args -join ' ')"
    & moodle-plugin-ci $name @args
    if ($LASTEXITCODE -ne 0) { $failed += $name }
}

if ($failed.Count) {
    Write-Host "`nFAILED: $($failed -join ', ')" -ForegroundColor Red
    exit 1
}
Write-Host "`nAll checks passed." -ForegroundColor Green
