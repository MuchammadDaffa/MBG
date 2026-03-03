param(
    [string]$OutputFile = "CHAT_CONTEXT_AUTO.md",
    [string]$NextTask = ""
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Resolve-NextTask {
    param(
        [string]$RepoRoot,
        [string]$ManualNextTask
    )

    if ($ManualNextTask -and $ManualNextTask.Trim()) {
        return $ManualNextTask.Trim()
    }

    $nextTaskFile = Join-Path $RepoRoot "NEXT_TASK.txt"
    if (Test-Path $nextTaskFile) {
        $nextTaskText = (Get-Content -Path $nextTaskFile -Raw).Trim()
        if ($nextTaskText) {
            return $nextTaskText
        }
    }

    $todoFiles = @(
        (Join-Path $RepoRoot "TODO.md"),
        (Join-Path $RepoRoot "TODO.txt"),
        (Join-Path $RepoRoot "TASKS.md")
    )

    foreach ($file in $todoFiles) {
        if (-not (Test-Path $file)) {
            continue
        }

        $lines = Get-Content -Path $file
        $openTask = $lines |
            Where-Object { $_ -match "^\s*[-*]\s*\[\s\]\s+" -or $_ -match "^\s*[-*]\s+" } |
            Select-Object -First 1

        if ($openTask) {
            $cleanTask = $openTask -replace "^\s*[-*]\s*\[\s\]\s+", "" -replace "^\s*[-*]\s+", ""
            $cleanTask = $cleanTask.Trim()
            if ($cleanTask) {
                return $cleanTask
            }
        }
    }

    return "Isi next step di sini sebelum mulai chat baru."
}

function Get-GitExecutable {
    $gitFromPath = Get-Command git -ErrorAction SilentlyContinue
    if ($gitFromPath) {
        return "git"
    }

    $fallback = "C:\Program Files\Git\cmd\git.exe"
    if (Test-Path $fallback) {
        return $fallback
    }

    throw "Git tidak ditemukan. Install Git terlebih dahulu."
}

function Invoke-Git {
    param(
        [string]$GitExe,
        [string[]]$GitArgs,
        [switch]$AllowFail
    )

    $result = & $GitExe @GitArgs 2>&1
    $exitCode = $LASTEXITCODE
    $text = ($result | Out-String).Trim()

    if (-not $AllowFail -and $exitCode -ne 0) {
        throw "Perintah git gagal: git $($GitArgs -join ' ')`n$text"
    }

    return $text
}

$git = Get-GitExecutable
$repoRootRaw = Invoke-Git -GitExe $git -GitArgs @("rev-parse", "--show-toplevel")
$repoRoot = ($repoRootRaw -split "`r?`n" |
    Where-Object { $_ -match "^[A-Za-z]:[\\/]" -or $_ -match "^/" } |
    Select-Object -First 1)

if (-not $repoRoot) {
    throw "Folder saat ini bukan repository Git. Jalankan script dari folder proyek."
}

Set-Location $repoRoot

$generatedAt = Get-Date -Format "yyyy-MM-dd HH:mm:ss zzz"
$branch = Invoke-Git -GitExe $git -GitArgs @("branch", "--show-current")
$remote = Invoke-Git -GitExe $git -GitArgs @("remote", "get-url", "origin") -AllowFail
$head = Invoke-Git -GitExe $git -GitArgs @("log", "-1", "--pretty=format:%h | %ad | %an | %s", "--date=iso") -AllowFail
$recentCommits = Invoke-Git -GitExe $git -GitArgs @("log", "-5", "--pretty=format:- %h %ad %s", "--date=short") -AllowFail
$status = Invoke-Git -GitExe $git -GitArgs @("status", "--short")
$staged = Invoke-Git -GitExe $git -GitArgs @("diff", "--cached", "--name-only")
$unstaged = Invoke-Git -GitExe $git -GitArgs @("diff", "--name-only")
$untracked = Invoke-Git -GitExe $git -GitArgs @("ls-files", "--others", "--exclude-standard")

$topLevelDirs = Get-ChildItem -Directory -Force |
    Where-Object { $_.Name -ne ".git" } |
    Select-Object -ExpandProperty Name

if (-not $status) { $status = "(clean)" }
if (-not $staged) { $staged = "(none)" }
if (-not $unstaged) { $unstaged = "(none)" }
if (-not $untracked) { $untracked = "(none)" }
if (-not $recentCommits) { $recentCommits = "(no commits yet)" }
$NextTask = Resolve-NextTask -RepoRoot $repoRoot -ManualNextTask $NextTask

$dirTree = if ($topLevelDirs.Count -gt 0) {
    ($topLevelDirs | ForEach-Object { "- $_/" }) -join "`n"
}
else {
    "- (no folders)"
}

$content = @"
# CHAT CONTEXT AUTO

Generated at: $generatedAt

## Quick Project Snapshot
- Repo root: $repoRoot
- Branch aktif: $branch
- Remote origin: $remote
- Last commit: $head

## Workspace Folders
$dirTree

## Git Status
~~~text
$status
~~~

## Staged Files
~~~text
$staged
~~~

## Unstaged Files
~~~text
$unstaged
~~~

## Untracked Files
~~~text
$untracked
~~~

## Recent Commits (5)
~~~text
$recentCommits
~~~

## Architecture Decisions (Current)
- Aplikasi inventory MBG berbasis web cloud centralized.
- Stack: Laravel API + Vue.js + PostgreSQL.
- Multi lokasi, multi user.
- Role: staff, admin_lokasi, admin_pusat.
- Sistem stok berbasis ledger (stok dihitung dari mutasi, bukan angka manual).
- Semua tabel transaksi wajib memiliki lokasi_id.

## Next Task
$NextTask

## Prompt untuk Chat Baru (Copy-Paste)
~~~text
Lanjutkan proyek MBG Inventory dari konteks berikut.

File konteks: CHAT_CONTEXT_AUTO.md (terbaru)
Target hari ini: $NextTask

Gunakan keputusan arsitektur yang sudah ada:
- Laravel API + Vue.js + PostgreSQL
- Multi lokasi + role staff/admin_lokasi/admin_pusat
- Ledger stock + semua transaksi wajib lokasi_id

Mohon langsung lanjut eksekusi berdasarkan status repo saat ini.
~~~
"@

Set-Content -Path $OutputFile -Value $content -Encoding UTF8

Write-Host "Berhasil membuat $OutputFile"
Write-Host "Tip: Jalankan script ini setiap selesai sesi kerja sebelum pindah chat."
