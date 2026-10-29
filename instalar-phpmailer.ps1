# Execute pelo PowerShell dentro da pasta support-desk.
$ErrorActionPreference = 'Stop'
$base = Split-Path -Parent $MyInvocation.MyCommand.Path
$uri = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.12.0.zip'
$temp = Join-Path $env:TEMP ('sd_phpmailer_' + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $temp | Out-Null
try {
    $archive = Join-Path $temp 'phpmailer.zip'
    Invoke-WebRequest -Uri $uri -OutFile $archive -UseBasicParsing
    Expand-Archive -Path $archive -DestinationPath $temp
    $src = Join-Path $temp 'PHPMailer-6.12.0\src'
    if (-not (Test-Path (Join-Path $src 'PHPMailer.php'))) { throw 'Biblioteca nao encontrada no arquivo baixado.' }
    $dest = Join-Path $base 'vendor\phpmailer\src'
    New-Item -ItemType Directory -Force -Path $dest | Out-Null
    Copy-Item -Path (Join-Path $src 'PHPMailer.php'), (Join-Path $src 'SMTP.php'), (Join-Path $src 'Exception.php') -Destination $dest -Force
    Write-Host 'PHPMailer instalado com sucesso!' -ForegroundColor Green
} finally { Remove-Item -LiteralPath $temp -Recurse -Force -ErrorAction SilentlyContinue }
