$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot '.env'
$composePath = Join-Path $projectRoot 'docker-compose.yml'

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Docker nao foi encontrado. Instale e abra o Docker Desktop antes de continuar.'
}

if (Test-Path $envPath) {
    $environmentLines = @(Get-Content -Path $envPath)
    Write-Host 'Arquivo .env encontrado; as credenciais existentes serao preservadas.'
}
else {
    $environmentLines = @()
}

$defaults = [ordered]@{
    MYSQL_ROOT_PASSWORD = [Guid]::NewGuid().ToString('N') + [Guid]::NewGuid().ToString('N')
    MYSQL_PASSWORD = [Guid]::NewGuid().ToString('N') + [Guid]::NewGuid().ToString('N')
    POSTGRES_PASSWORD = [Guid]::NewGuid().ToString('N') + [Guid]::NewGuid().ToString('N')
    AUTHENTICATION_API_KEY = [Guid]::NewGuid().ToString('N') + [Guid]::NewGuid().ToString('N')
    APP_BIND_ADDRESS = '127.0.0.1'
    APP_PORT = '8081'
}

$updatedEnvironment = $false
foreach ($name in $defaults.Keys) {
    $matchedLine = -1
    for ($index = 0; $index -lt $environmentLines.Count; $index++) {
        if ($environmentLines[$index] -match ('^\s*' + [regex]::Escape($name) + '\s*=\s*(.*?)\s*$')) {
            $matchedLine = $index
            $value = $Matches[1]
            break
        }
    }

    if ($matchedLine -lt 0) {
        $environmentLines += "$name=$($defaults[$name])"
        $value = $defaults[$name]
        $updatedEnvironment = $true
    }
    elseif ([string]::IsNullOrWhiteSpace($value) -or $value.StartsWith('replace-with-')) {
        $value = $defaults[$name]
        $environmentLines[$matchedLine] = "$name=$value"
        $updatedEnvironment = $true
    }

    if (($name -in @('MYSQL_ROOT_PASSWORD', 'MYSQL_PASSWORD', 'POSTGRES_PASSWORD', 'AUTHENTICATION_API_KEY')) -and ($value.Length -lt 32 -or $value.StartsWith('replace-with-'))) {
        throw "A variavel $name no arquivo .env deve ter pelo menos 32 caracteres aleatorios."
    }
}

if ($updatedEnvironment) {
    Set-Content -Path $envPath -Value $environmentLines -Encoding ASCII
    Write-Host 'Credenciais Docker verificadas/adicionadas ao .env local, que nao sera publicado no Git.'
}

$environmentValues = @{}
foreach ($line in $environmentLines) {
    if ($line -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*?)\s*$') {
        $environmentValues[$Matches[1]] = $Matches[2]
    }
}

$appPort = 0
if ((-not [int]::TryParse([string] $environmentValues['APP_PORT'], [ref] $appPort)) -or $appPort -lt 1 -or $appPort -gt 65535) {
    throw 'APP_PORT no arquivo .env deve ser uma porta entre 1 e 65535.'
}

Push-Location $projectRoot
try {
    docker compose --file $composePath config --quiet
    if ($LASTEXITCODE -ne 0) {
        throw 'A configuracao do Docker Compose e invalida.'
    }

    docker compose --file $composePath up -d --build
    if ($LASTEXITCODE -ne 0) {
        throw 'Nao foi possivel iniciar a aplicacao. Verifique se o Docker Desktop esta iniciado.'
    }

    $appReady = $false
    for ($attempt = 0; $attempt -lt 180; $attempt++) {
        try {
            $response = Invoke-WebRequest -Uri "http://127.0.0.1:$($environmentValues['APP_PORT'])/healthz" -TimeoutSec 3 -UseBasicParsing
            if ($response.StatusCode -eq 200 -and $response.Content.Trim() -eq 'ok') {
                $appReady = $true
                break
            }
        }
        catch {
            Start-Sleep -Seconds 2
        }
    }

    if (-not $appReady) {
        docker compose --file $composePath ps
        throw 'A aplicacao NPS nao ficou pronta dentro de 6 minutos. Consulte docker compose logs nps-app nps-db.'
    }
}
finally {
    Pop-Location
}

$appUrl = "http://localhost:$($environmentValues['APP_PORT'])"
Write-Host ''
Write-Host "Aplicacao NPS pronta em $appUrl"
Write-Host 'Entre em /setup para criar a conta MASTER, caso ainda nao exista.'
Write-Host 'A Evolution API pode continuar preparando o banco na primeira inicializacao; acompanhe com docker compose logs -f evolution-api.'
Write-Host 'Os dados SQL existentes do XAMPP nao sao apagados nem copiados automaticamente para o novo volume MySQL.'
