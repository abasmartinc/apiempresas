while ($true) {
    Write-Host "Lanzando script PHP..."
    php recover_borme_orphans.php
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Script terminado con éxito."
        break
    }
    Write-Host "Conexión perdida (MySQL server has gone away). Reanudando en 5 segundos..."
    Start-Sleep -Seconds 5
}
