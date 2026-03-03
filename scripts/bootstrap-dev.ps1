param(
    [string]$ProjectRoot = ".",
    [string]$RestoreDumpPath = "",
    [switch]$Seed
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Get-ComposerRunner {
    param([string]$RepoRoot)

    $composerCmd = Get-Command composer -ErrorAction SilentlyContinue
    if ($composerCmd) {
        return @("composer")
    }

    $toolsDir = Join-Path $RepoRoot ".tools"
    $pharPath = Join-Path $toolsDir "composer.phar"

    if (-not (Test-Path $pharPath)) {
        if (-not (Test-Path $toolsDir)) {
            New-Item -ItemType Directory -Path $toolsDir | Out-Null
        }

        $installer = Join-Path $toolsDir "composer-setup.php"
        Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile $installer

        & php $installer --install-dir=$toolsDir --filename=composer.phar
        if ($LASTEXITCODE -ne 0) {
            throw "Gagal menginstal composer.phar"
        }
    }

    return @("php", $pharPath)
}

function Read-EnvValue {
    param(
        [string]$EnvPath,
        [string]$Key,
        [string]$Default = ""
    )

    $line = Get-Content -Path $EnvPath |
        Where-Object { $_ -match "^$([Regex]::Escape($Key))=" } |
        Select-Object -First 1

    if (-not $line) {
        return $Default
    }

    $value = $line.Substring($Key.Length + 1).Trim()
    $value = $value.Trim('"')
    if ($value -eq "") {
        return $Default
    }

    return $value
}

function Find-PostgresTool {
    param([string]$Name)

    $cmd = Get-Command $Name -ErrorAction SilentlyContinue
    if ($cmd) {
        return $cmd.Source
    }

    $matches = Get-ChildItem -Path "C:\Program Files\PostgreSQL" -Recurse -Filter "$Name.exe" -ErrorAction SilentlyContinue |
        Sort-Object FullName -Descending

    if ($matches -and $matches.Count -gt 0) {
        return $matches[0].FullName
    }

    return ""
}

$repoRoot = (Resolve-Path $ProjectRoot).Path
$backendPath = Join-Path $repoRoot "backend"

if (-not (Test-Path $backendPath)) {
    throw "Folder backend tidak ditemukan di: $backendPath"
}

$envPath = Join-Path $backendPath ".env"
$envExamplePath = Join-Path $backendPath ".env.example"

if (-not (Test-Path $envPath)) {
    Copy-Item -Path $envExamplePath -Destination $envPath
}

$composerRunner = Get-ComposerRunner -RepoRoot $repoRoot
Push-Location $backendPath

try {
    if ($composerRunner.Length -eq 1) {
        & $composerRunner[0] install --no-interaction
    }
    else {
        & $composerRunner[0] $composerRunner[1] install --no-interaction
    }
    if ($LASTEXITCODE -ne 0) {
        throw "Composer install gagal"
    }

    & php artisan key:generate --force
    if ($LASTEXITCODE -ne 0) {
        throw "Gagal generate APP_KEY"
    }

    $dbHost = Read-EnvValue -EnvPath $envPath -Key "DB_HOST" -Default "127.0.0.1"
    $dbPort = Read-EnvValue -EnvPath $envPath -Key "DB_PORT" -Default "5432"
    $dbName = Read-EnvValue -EnvPath $envPath -Key "DB_DATABASE" -Default "mbg_inventory"
    $dbUser = Read-EnvValue -EnvPath $envPath -Key "DB_USERNAME" -Default "postgres"
    $dbPassword = Read-EnvValue -EnvPath $envPath -Key "DB_PASSWORD" -Default ""

    $psql = Find-PostgresTool -Name "psql"
    if (-not $psql) {
        throw "psql tidak ditemukan. Pastikan PostgreSQL terinstal."
    }

    $previousPgPassword = $env:PGPASSWORD
    if ($dbPassword) {
        $env:PGPASSWORD = $dbPassword
    }

    $escapedDbName = $dbName.Replace("'", "''")
    $exists = & $psql -h $dbHost -p $dbPort -U $dbUser -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='$escapedDbName';"
    $existsText = ($exists | Out-String).Trim()

    if ($existsText -ne "1") {
        $safeName = $dbName.Replace('"', '""')
        & $psql -h $dbHost -p $dbPort -U $dbUser -d postgres -c "CREATE DATABASE \"$safeName\";"
        if ($LASTEXITCODE -ne 0) {
            throw "Gagal membuat database $dbName"
        }
    }

    & php artisan config:clear
    if ($LASTEXITCODE -ne 0) {
        throw "Gagal menjalankan config:clear"
    }

    if ($RestoreDumpPath -and (Test-Path $RestoreDumpPath)) {
        $pgRestore = Find-PostgresTool -Name "pg_restore"
        if (-not $pgRestore) {
            throw "pg_restore tidak ditemukan."
        }

        & $pgRestore -h $dbHost -p $dbPort -U $dbUser -d $dbName --clean --if-exists $RestoreDumpPath
        if ($LASTEXITCODE -ne 0) {
            throw "Gagal restore dump database"
        }
    }
    else {
        & php artisan migrate --force
        if ($LASTEXITCODE -ne 0) {
            throw "Gagal menjalankan migrate"
        }

        if ($Seed) {
            & php artisan db:seed --force
            if ($LASTEXITCODE -ne 0) {
                throw "Gagal menjalankan db:seed"
            }
        }
    }

    Write-Host "Bootstrap development selesai."
}
finally {
    if ($null -ne $previousPgPassword) {
        $env:PGPASSWORD = $previousPgPassword
    }
    else {
        Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
    }

    Pop-Location
}
