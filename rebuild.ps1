#!/usr/bin/env pwsh
# Rebuild script for MythicalDash after code changes

Write-Host "Rebuilding MythicalDash backend and frontend..." -ForegroundColor Cyan

# Pull latest code
Write-Host "`nPulling latest code..." -ForegroundColor Yellow
git pull

# Rebuild and restart containers
Write-Host "`nRebuilding Docker containers..." -ForegroundColor Yellow
docker compose -f docker-compose-dev.yml up --build backend frontend -d

# Wait for containers to be healthy
Write-Host "`nWaiting for containers to become healthy..." -ForegroundColor Yellow
$maxAttempts = 30
$attempt = 0

while ($attempt -lt $maxAttempts) {
    $backendHealth = docker inspect --format='{{.State.Health.Status}}' mythicaldash_v3_backend 2>$null
    $frontendStatus = docker inspect --format='{{.State.Status}}' mythicaldash_v3_frontend 2>$null

    if ($backendHealth -eq "healthy" -and $frontendStatus -eq "running") {
        Write-Host "`n✓ Containers are healthy and running!" -ForegroundColor Green
        Write-Host "`nYou can now test the login at http://localhost:3000" -ForegroundColor Green
        break
    }

    $attempt++
    Write-Host "Attempt $attempt/$maxAttempts - Backend: $backendHealth, Frontend: $frontendStatus" -ForegroundColor Gray
    Start-Sleep -Seconds 2
}

if ($attempt -eq $maxAttempts) {
    Write-Host "`n✗ Containers did not become healthy within timeout" -ForegroundColor Red
    Write-Host "Check logs with: docker compose -f docker-compose-dev.yml logs backend" -ForegroundColor Yellow
}
