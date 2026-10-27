# Pushes ONLY the incentive-system/ folder's history to its own GitHub repo.
# The local git repo covers the whole "claud" folder, so the project is split out first.
#
# Usage:
#   powershell -ExecutionPolicy Bypass -File deploy\push-to-github.ps1                      # push what is already committed
#   powershell -ExecutionPolicy Bypass -File deploy\push-to-github.ps1 -Message "my change" # commit pending changes in this folder first
#
# Authentication is whatever Git Credential Manager has saved for github.com (browser sign-in
# or a personal access token). Nothing is stored in this script.
param(
    [string]$Message = '',
    [string]$Remote  = 'https://github.com/cresentechno-hub/incentive.git',
    [string]$Branch  = 'main'
)

$projectDir = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$repo   = (& git -C $projectDir rev-parse --show-toplevel).Trim()
$prefix = (& git -C $projectDir rev-parse --show-prefix).Trim().TrimEnd('/')
$split  = 'incentive-split'

# 1. Commit pending changes (only inside this project) if a message was given
$pending = & git -C $repo status --porcelain -- $prefix
if ($pending) {
    if (-not $Message) {
        Write-Host "Uncommitted changes in ${prefix}:" -ForegroundColor Yellow
        $pending | ForEach-Object { Write-Host "  $_" }
        Write-Host "Re-run with -Message 'describe the change' to commit them, or commit manually." -ForegroundColor Yellow
        exit 1
    }
    & git -C $repo add -- $prefix
    & git -C $repo commit -m $Message
    if ($LASTEXITCODE -ne 0) { Write-Host 'Commit failed.' -ForegroundColor Red; exit 1 }
}

# 2. Split this folder's history onto a temporary branch (same commit hashes every run,
#    so later pushes are normal fast-forwards)
& git -C $repo branch -D $split 2>$null | Out-Null
& git -C $repo subtree split --prefix=$prefix -b $split | Out-Null
if ($LASTEXITCODE -ne 0) { Write-Host 'subtree split failed.' -ForegroundColor Red; exit 1 }

# 3. Push, then remove the temporary branch
& git -C $repo push $Remote "${split}:${Branch}"
$code = $LASTEXITCODE
& git -C $repo branch -D $split 2>$null | Out-Null

if ($code -ne 0) {
    Write-Host "Push failed (exit $code). A 403 means the saved GitHub login has no write access to the repo." -ForegroundColor Red
    exit $code
}
Write-Host "Pushed to $Remote ($Branch)." -ForegroundColor Green
