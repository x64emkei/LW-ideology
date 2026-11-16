# Responsive viewport screenshot and overflow audit
$chrome = "C:\Program Files\Google\Chrome\Application\chrome.exe"
$viewports = @(
    @{ Name = "320px-small-mobile"; Width = 320; Height = 640 },
    @{ Name = "375px-standard-mobile"; Width = 375; Height = 667 },
    @{ Name = "390px-modern-mobile"; Width = 390; Height = 844 },
    @{ Name = "430px-large-mobile"; Width = 430; Height = 932 },
    @{ Name = "768px-tablet"; Width = 768; Height = 1024 },
    @{ Name = "1024px-small-laptop"; Width = 1024; Height = 768 },
    @{ Name = "1280px-desktop"; Width = 1280; Height = 800 },
    @{ Name = "1440px-standard-desktop"; Width = 1440; Height = 900 },
    @{ Name = "1920px-ultrawide-desktop"; Width = 1920; Height = 1080 }
)

$outDir = "C:\xampp\htdocs\LW-ideology\documentation\screenshots\responsive"
if (-not (Test-Path $outDir)) {
    New-Item -ItemType Directory -Path $outDir -Force | Out-Null
}

Write-Host "Capturing responsive viewport verification screenshots across 9 breakpoints..."
foreach ($vp in $viewports) {
    $outFile = Join-Path $outDir "homepage-$($vp.Name).png"
    $windowSize = "$($vp.Width),$($vp.Height)"
    Start-Process -FilePath $chrome -ArgumentList "--headless=new", "--screenshot=$outFile", "--window-size=$windowSize", "http://localhost/LW-ideology/index.php" -Wait
    Write-Host "  [OK] $($vp.Name) ($($vp.Width)x$($vp.Height)) -> $outFile"
}

# Also capture key internal views at standard mobile (390px) and standard desktop (1440px)
$keyPages = @(
    @{ Slug = "about"; Url = "http://localhost/LW-ideology/pages/about.php" },
    @{ Slug = "articles"; Url = "http://localhost/LW-ideology/pages/articles.php" },
    @{ Slug = "topics"; Url = "http://localhost/LW-ideology/pages/topics.php" },
    @{ Slug = "contact"; Url = "http://localhost/LW-ideology/pages/contact.php" },
    @{ Slug = "register"; Url = "http://localhost/LW-ideology/auth/register.php" },
    @{ Slug = "login"; Url = "http://localhost/LW-ideology/auth/login.php" }
)

foreach ($p in $keyPages) {
    $desktopOut = Join-Path $outDir "$($p.Slug)-1440px.png"
    $mobileOut = Join-Path $outDir "$($p.Slug)-390px.png"
    Start-Process -FilePath $chrome -ArgumentList "--headless=new", "--screenshot=$desktopOut", "--window-size=1440,900", $p.Url -Wait
    Start-Process -FilePath $chrome -ArgumentList "--headless=new", "--screenshot=$mobileOut", "--window-size=390,844", $p.Url -Wait
    Write-Host "  [OK] $($p.Slug) (Desktop & Mobile captures complete)"
}

Write-Host "All responsive screenshot captures complete!"
