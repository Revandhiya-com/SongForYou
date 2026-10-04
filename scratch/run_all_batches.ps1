$totalBatches = 10
Write-Host "Starting batched migration of 776 songs to Supabase..."
Start-Sleep -Seconds 20  # Wait for Vercel to deploy

for ($batch = 0; $batch -lt $totalBatches; $batch++) {
    Write-Host "Running batch $batch of $($totalBatches - 1)..."
    try {
        $result = Invoke-RestMethod -Uri "https://songforyou-sigma.vercel.app/run-migration?batch=$batch" -TimeoutSec 30
        $result | ConvertTo-Json
        if ($result.done -eq $true) {
            Write-Host "MIGRATION COMPLETE!"
            break
        }
    } catch {
        Write-Host "ERROR on batch $batch`: $_"
    }
    Start-Sleep -Seconds 2
}
