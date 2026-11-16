# ==============================================================================
# LagunaWISE — Ideology Division: Automated FTP Deployment (PowerShell)
# ==============================================================================

$ftpHost = "ftpupload.net"
$ftpPort = 21
$ftpUser = "if0_43456590"
$ftpPass = "Goq4bpkrc1"
$remoteRoot = "/htdocs"
$localDir = "C:\xampp\htdocs\LW-ideology-infinityfree"

Write-Host "Starting automated deployment to InfinityFree via FTP..." -ForegroundColor Cyan
& python "C:\xampp\htdocs\LW-ideology\cli\ftp_deploy.py"
