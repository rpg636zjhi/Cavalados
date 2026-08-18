param(
    [Parameter(Mandatory = $true)]
    [string] $Archive,

    [Parameter(Mandatory = $true)]
    [ValidatePattern('^[0-9a-fA-F]{64}$')]
    [string] $Sha256
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$Target = Join-Path $Root "bin\php7"

function Stop-Install([string] $Message) {
    Write-Host "ERRO: $Message" -ForegroundColor Red
    exit 1
}

if (-not [Environment]::Is64BitOperatingSystem) {
    Stop-Install "o runtime legado requer Windows x64."
}
if (-not (Test-Path -LiteralPath $Archive -PathType Leaf)) {
    Stop-Install "o pacote informado não existe."
}
if (Test-Path $Target) {
    Stop-Install "a pasta '$Target' já existe; ela não foi substituída."
}

$ActualSha256 = (Get-FileHash -Algorithm SHA256 -LiteralPath $Archive).Hash.ToLowerInvariant()
if ($ActualSha256 -ne $Sha256.ToLowerInvariant()) {
    Stop-Install "o SHA-256 do pacote informado não confere."
}

$Temp = Join-Path ([IO.Path]::GetTempPath()) ("cavalados-php7-" + [Guid]::NewGuid().ToString("N"))
try {
    New-Item -ItemType Directory -Path $Temp -Force | Out-Null
    Write-Host "[Cavalados] Extraindo e validando o runtime legado..." -ForegroundColor Green
    Expand-Archive -LiteralPath $Archive -DestinationPath $Temp -Force
    $Php = Get-ChildItem -Path $Temp -Filter "php.exe" -File -Recurse | Select-Object -First 1
    if ($null -eq $Php) {
        Stop-Install "o pacote não contém php.exe."
    }

    $Ini = Join-Path $Php.DirectoryName "php.ini"
    $Arguments = @()
    if (Test-Path $Ini) {
        $Arguments += @("-c", $Ini)
    }
    $Version = (& $Php.FullName @Arguments -r 'echo PHP_VERSION;').Trim()
    $Pthreads = (& $Php.FullName @Arguments -r 'echo extension_loaded("pthreads") ? "yes" : "no";').Trim()
    if (-not $Version.StartsWith("7.") -or $Pthreads -ne "yes") {
        Stop-Install "o pacote precisa conter PHP 7 x64 com pthreads."
    }

    New-Item -ItemType Directory -Path (Split-Path -Parent $Target) -Force | Out-Null
    Move-Item -LiteralPath $Php.DirectoryName -Destination $Target
    Write-Host "Instalação concluída com PHP $Version." -ForegroundColor Green
    Write-Host "Use .\start.cmd para iniciar o servidor." -ForegroundColor Yellow
}
finally {
    if (Test-Path $Temp) {
        Remove-Item -LiteralPath $Temp -Recurse -Force
    }
}
