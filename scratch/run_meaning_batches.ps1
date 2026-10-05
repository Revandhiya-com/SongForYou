param(
    [string]$BaseUrl = 'https://songforyou-sigma.vercel.app/backend/update_all_meanings.php',
    [int]$BatchSize = 3,
    [int]$MaxBatches = 300
)

$afterId = 0
for ($batch = 1; $batch -le $MaxBatches; $batch++) {
    $url = "$BaseUrl?after_id=$afterId&limit=$BatchSize"
    $result = Invoke-RestMethod -Uri $url -TimeoutSec 45
    $result | ConvertTo-Json -Compress

    if (-not $result.success) {
        throw "Pembaruan makna gagal pada batch $batch."
    }
    if ($result.done) {
        Write-Host 'Pembaruan makna selesai.'
        break
    }

    $afterId = [int]$result.nextAfterId
    Start-Sleep -Milliseconds 250
}
