$projectDir = "C:\Users\Acer\Desktop\IMPORT FILES\flowchart\group-3"
$desktopDir = [System.Environment]::GetFolderPath('Desktop')
$cloudflaredPath = "$projectDir\cloudflared.exe"

# 1. Start MySQL if not running
$mysql = Get-Process -Name mysqld -ErrorAction SilentlyContinue
if (-not $mysql) {
    Write-Host "[1/3] Starting MySQL Server..." -ForegroundColor Cyan
    Start-Process -FilePath "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini --standalone" -WindowStyle Hidden
    Start-Sleep -Seconds 2
} else {
    Write-Host "[1/3] MySQL Server is already running." -ForegroundColor Green
}

# 2. Check Herd group-3.test
Write-Host "[2/3] Checking Herd Local Site (group-3.test)..." -ForegroundColor Cyan
try {
    $test = Invoke-WebRequest -Uri "https://group-3.test" -SkipCertificateCheck -TimeoutSec 3 -ErrorAction SilentlyContinue
    Write-Host "[OK] Local site group-3.test is active!" -ForegroundColor Green
} catch {
    Write-Host "[!] Note: Ensure Herd is running." -ForegroundColor Yellow
}

# 3. Launch Cloudflared Tunnel
Write-Host "[3/3] Starting Cloudflare Live Tunnel..." -ForegroundColor Cyan

$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = $cloudflaredPath
$psi.Arguments = "tunnel --url https://127.0.0.1:443 --no-tls-verify --http-host-header group-3.test"
$psi.RedirectStandardError = $true
$psi.RedirectStandardOutput = $true
$psi.UseShellExecute = $false
$psi.CreateNoWindow = $true

$proc = [System.Diagnostics.Process]::Start($psi)
$tunnelUrl = $null

# Background reader for tunnel URL
while (-not $proc.HasExited) {
    $line = $proc.StandardError.ReadLine()
    if ($null -eq $line) {
        Start-Sleep -Milliseconds 100
        continue
    }
    
    if ($line -match "https://([a-zA-Z0-9-]+)\.trycloudflare\.com" -and $matches[1] -ne "api") {
        $tunnelUrl = $matches[0]
        break
    }
}

if ($tunnelUrl) {
    # Save to Desktop
    $txtPath = Join-Path $desktopDir "TUNNEL_LINK_GROUP_3.txt"
    $lines = @(
        "========================================================================",
        "                GROUP 3 LIVE TUNNEL LINK (PELICLE)",
        "========================================================================",
        "",
        "LIVE URL:       $tunnelUrl",
        "EMPLOYEE LOGIN: $tunnelUrl/employee",
        "ADMIN LOGIN:    $tunnelUrl/admin",
        "GUARD SCANNER:  $tunnelUrl/guard/scanner",
        "",
        "PAALALA:",
        "- Kahit baguhin o i-save ang code sa VS Code, HINDI MAGBABAGO ANG LINK NA ITO!",
        "- Naka-live sync ito sa Herd. Refresh (F5) lang sa browser o phone.",
        "- Huwag isara ang black/blue window ng tunnel habang nagte-test.",
        "========================================================================"
    )
    $lines | Set-Content -Path $txtPath -Encoding UTF8

    # Create Internet Shortcut on Desktop
    $shortcutPath = Join-Path $desktopDir "Group 3 Live Tunnel.url"
    $shortcutLines = @(
        "[InternetShortcut]",
        "URL=" + $tunnelUrl
    )
    $shortcutLines | Set-Content -Path $shortcutPath -Encoding ASCII

    # Copy to clipboard
    try { Set-Clipboard -Value $tunnelUrl } catch {}

    Clear-Host
    Write-Host "========================================================================" -ForegroundColor Green
    Write-Host "                GROUP 3 LIVE TUNNEL IS RUNNING AND ACTIVE!              " -ForegroundColor Yellow
    Write-Host "========================================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "  LIVE URL:       $tunnelUrl" -ForegroundColor Cyan
    Write-Host "  EMPLOYEE LOGIN: $tunnelUrl/employee" -ForegroundColor White
    Write-Host "  ADMIN LOGIN:    $tunnelUrl/admin" -ForegroundColor White
    Write-Host "  GUARD SCANNER:  $tunnelUrl/guard/scanner" -ForegroundColor White
    Write-Host ""
    Write-Host "  ----------------------------------------------------------------------" -ForegroundColor DarkGray
    Write-Host "  [OK] KOPYADO NA SA CLIPBOARD ANG LINK! (Handa nang i-paste / Ctrl+V)" -ForegroundColor Green
    Write-Host "  [OK] Na-save din sa Desktop: Group 3 Live Tunnel.url at TUNNEL_LINK_GROUP_3.txt" -ForegroundColor Green
    Write-Host ""
    Write-Host "  * PAANO KAPAG MAY BINAGONG CODE SA VS CODE?" -ForegroundColor Yellow
    Write-Host "  -> HINDI KAILANGANG I-RESTART ANG TUNNEL NA ITO!" -ForegroundColor White
    Write-Host "  -> Diretso nang magre-reflect ang bagong code. I-refresh (F5) lang ang browser/phone!" -ForegroundColor White
    Write-Host "========================================================================" -ForegroundColor Green
    Write-Host "Huwag isara ang window na ito para manatiling online ang link." -ForegroundColor DarkYellow
    Write-Host "Press Ctrl + C para i-stop ang tunnel kapag tapos na." -ForegroundColor Gray
    Write-Host ""
}

# Wait for process exit and pipe remaining logs if needed
$proc.WaitForExit()
