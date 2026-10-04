Write-Host "Starting migration - waiting 20s for Vercel deploy..."
Start-Sleep -Seconds 20

for ($i = 0; $i -lt 10; $i++) {
    Write-Host "--- Batch $i ---"
    try {
        $url = "https://songforyou-sigma.vercel.app/run-migration?batch=$i"
        $r = Invoke-RestMethod -Uri $url -TimeoutSec 30
        $r | ConvertTo-Json
        if ($r.done -eq $true) {
            Write-Host "=== MIGRATION COMPLETE ==="
            break
        }
    } catch {
        Write-Host "ERROR on batch $i`: $_"
    }
    Start-Sleep -Seconds 2
}
Write-Host "Script finished."
