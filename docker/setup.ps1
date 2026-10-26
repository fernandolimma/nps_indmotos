$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot '.env'
$composePath = Join-Path $projectRoot 'docker-compose.yml'

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Docker nao foi encontrado. Instale e abra o Docker Desktop antes de continuar.'
}

if (-not (Test-Path $envPath)) {
    $postgresPassword = [Guid]::NewGuid().ToString('N') + [Guid]::NewGuid().ToString('N')
    $apiKey = [Guid]::NewGuid().ToString('N') + [Guid]::NewGuid().ToString('N')
    $environment = @(
        "POSTGRES_PASSWORD=$postgresPassword"
        "AUTHENTICATION_API_KEY=$apiKey"
    )
    Set-Content -Path $envPath -Value $environment -Encoding ASCII
    Write-Host 'Arquivo .env criado com credenciais aleatorias. Ele nao sera publicado no Git.'
}
else {
    Write-Host 'Arquivo .env existente encontrado; as credenciais salvas serao preservadas.'
}

$environmentValues = @{}
foreach ($line in Get-Content -Path $envPath) {
    if ($line -match '^\s*(POSTGRES_PASSWORD|AUTHENTICATION_API_KEY)\s*=\s*(.*?)\s*$') {
        $environmentValues[$Matches[1]] = $Matches[2]
    }
}

foreach ($name in @('POSTGRES_PASSWORD', 'AUTHENTICATION_API_KEY')) {
    $value = $environmentValues[$name]
    if ([string]::IsNullOrWhiteSpace($value) -or $value.Length -lt 32 -or $value.StartsWith('replace-with-')) {
        throw "A variavel $name no arquivo .env nao esta configurada com uma chave segura. Use chaves aleatorias com pelo menos 32 caracteres."
    }
}

Push-Location $projectRoot
try {
    docker compose --file $composePath config --quiet
    if ($LASTEXITCODE -ne 0) {
        throw 'A configuracao do Docker Compose e invalida.'
    }

    docker compose --file $composePath up -d
    if ($LASTEXITCODE -ne 0) {
        throw 'Nao foi possivel iniciar a Evolution API. Verifique se o Docker Desktop esta iniciado.'
    }

    $apiReady = $false
    for ($attempt = 0; $attempt -lt 300; $attempt++) {
        try {
            $response = Invoke-WebRequest -Uri 'http://127.0.0.1:8080/' -TimeoutSec 3 -UseBasicParsing
            if ($response.StatusCode -eq 200) {
                $apiReady = $true
                break
            }
        }
        catch {
            Start-Sleep -Seconds 2
        }
    }

    if (-not $apiReady) {
        docker compose --file $composePath ps
        throw 'Os containers foram iniciados, mas a Evolution API nao respondeu em http://127.0.0.1:8080 dentro de 10 minutos.'
    }
}
finally {
    Pop-Location
}

Write-Host ''
Write-Host 'Evolution API iniciada e respondendo em http://127.0.0.1:8080.'
Write-Host 'URL local: http://127.0.0.1:8080'
Write-Host 'Depois, volte a tela Configuracoes para criar a instancia e ler o QR code.'
