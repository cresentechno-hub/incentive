# Builds the cPanel upload package:
#   deploy\dist\incentive-system.zip   - app files (no setup.php, router.php, deploy/, .git, local config)
#   deploy\dist\incentive_system.sql   - clean DB dump, MariaDB/MySQL compatible, no CREATE DATABASE
# The dump comes from a temporary CLEAN database (schema + default rules + one admin);
# the local dev/demo database is never modified.
# Usage:  powershell -ExecutionPolicy Bypass -File deploy\build-package.ps1 -AdminUsername andyyoon -AdminPassword '...'
param(
    [Parameter(Mandatory)][string]$AdminUsername,
    [Parameter(Mandatory)][string]$AdminPassword,
    [string]$AdminName  = 'Administrator',
    [string]$AdminEmail = ''
)
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem

$root   = Split-Path $PSScriptRoot -Parent
$out    = Join-Path $PSScriptRoot 'dist'
$mysql  = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe'
$dump   = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe'
$dbName = 'incentive_system_prod'
New-Item -ItemType Directory -Force $out | Out-Null

# ── 1. Clean database -> dump ────────────────────────────────
$env:ADMIN_USERNAME = $AdminUsername; $env:ADMIN_PASSWORD = $AdminPassword
$env:ADMIN_NAME = $AdminName;         $env:PROD_DB = $dbName
if ($AdminEmail) { $env:ADMIN_EMAIL = $AdminEmail } else { Remove-Item Env:\ADMIN_EMAIL -ErrorAction SilentlyContinue }
try {
    php (Join-Path $PSScriptRoot 'make-prod-db.php')
    if ($LASTEXITCODE -ne 0) { throw 'make-prod-db.php failed' }
} finally {
    Remove-Item Env:\ADMIN_USERNAME, Env:\ADMIN_PASSWORD, Env:\ADMIN_NAME, Env:\PROD_DB -ErrorAction SilentlyContinue
}

$sqlFile = Join-Path $out 'incentive_system.sql'
& $dump -u root --password="" --no-tablespaces --skip-comments --skip-add-drop-table `
        --default-character-set=utf8mb4 --result-file="$sqlFile" $dbName
if ($LASTEXITCODE -ne 0) { throw 'mysqldump failed' }
& $mysql -u root --password="" -e "DROP DATABASE IF EXISTS ``$dbName``"

$utf8 = New-Object System.Text.UTF8Encoding($false)
$sql  = [System.IO.File]::ReadAllText($sqlFile, $utf8)
$sql  = $sql -replace 'utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci'      # MariaDB has no 0900 collation
$sql  = $sql -replace 'DEFINER=`[^`]*`@`[^`]*`', ''
[System.IO.File]::WriteAllText($sqlFile, $sql, $utf8)

# ── 2. App zip ───────────────────────────────────────────────
$zipFile = Join-Path $out 'incentive-system.zip'
if (Test-Path $zipFile) { Remove-Item $zipFile -Force }

$excludeDirs  = @('deploy', '.git', '.claude')
$excludeFiles = @('public/setup.php', 'public/router.php', 'SETUP.md', 'config.local.php', '.gitignore')

$zip = [System.IO.Compression.ZipFile]::Open($zipFile, 'Create')
try {
    Get-ChildItem $root -Recurse -File -Force | ForEach-Object {
        $rel = $_.FullName.Substring($root.Length + 1).Replace('\', '/')
        $top = $rel.Split('/')[0]
        if ($excludeDirs -contains $top)       { return }
        if ($excludeFiles -contains $rel)      { return }
        if ($rel -like 'storage/*.log')        { return }
        [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $rel, 'Optimal')
    }
} finally { $zip.Dispose() }

$z = [System.IO.Compression.ZipFile]::OpenRead($zipFile)
"{0} entries in zip ({1:N0} KB); SQL {2:N0} KB" -f $z.Entries.Count, ((Get-Item $zipFile).Length/1KB), ((Get-Item $sqlFile).Length/1KB)
$z.Dispose()
