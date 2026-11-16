param([string]$Project = 'C:\laragon\www\tokobaju')
$ErrorActionPreference = 'Stop'
$target = Join-Path $Project 'public\welcome'
if (!(Test-Path -LiteralPath (Join-Path $Project 'artisan') -PathType Leaf) -or !(Test-Path -LiteralPath $target -PathType Container)) { throw 'Folder Laravel atau public/welcome tidak ditemukan. Periksa parameter Project.' }
$photos = @(
    @{ Name = 'hero.jpg'; Id = '23531833'; Width = 1200 }
    @{ Name = 'hero-inset.jpg'; Id = '9514677'; Width = 1200 }
    @{ Name = 'about.jpg'; Id = '36829225'; Width = 1200 }
    @{ Name = 'category-atasan.jpg'; Id = '20867570'; Width = 1200 }
    @{ Name = 'category-bawahan.jpg'; Id = '31400266'; Width = 1200 }
    @{ Name = 'category-dress.jpg'; Id = '21837386'; Width = 1200 }
    @{ Name = 'category-outerwear.jpg'; Id = '38139796'; Width = 1200 }
    @{ Name = 'category-pakaian-pria.jpg'; Id = '15581106'; Width = 1200 }
    @{ Name = 'category-aksesori.jpg'; Id = '7742501'; Width = 1200 }
    @{ Name = 'promo.jpg'; Id = '8484057'; Width = 1800 }
)
foreach ($photo in $photos) {
    if (!(Test-Path -LiteralPath (Join-Path $target $photo.Name) -PathType Leaf)) { throw "Aset lama tidak ditemukan: $($photo.Name). Tidak ada file yang diubah." }
}
Add-Type -AssemblyName System.Drawing
$stage = Join-Path ([System.IO.Path]::GetTempPath()) ('velora-photos-' + [guid]::NewGuid().ToString('N'))
$backup = Join-Path (Split-Path -Parent $Project) ('velora-welcome-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '-' + [guid]::NewGuid().ToString('N').Substring(0,6))
New-Item -ItemType Directory -Path $stage | Out-Null
try {
    foreach ($photo in $photos) {
        $id = $photo.Id
        $width = $photo.Width
        $url = "https://images.pexels.com/photos/$id/pexels-photo-$id.jpeg?auto=compress&cs=tinysrgb&w=$width"
        $temp = Join-Path $stage $photo.Name
        Write-Host "Downloading $($photo.Name) from Pexels ..."
        Invoke-WebRequest -Uri $url -OutFile $temp -UseBasicParsing -TimeoutSec 60
        $img = [System.Drawing.Image]::FromFile($temp)
        try {
            if ($img.RawFormat.Guid -ne [System.Drawing.Imaging.ImageFormat]::Jpeg.Guid -or $img.Width -lt 500 -or $img.Height -lt 300) { throw "Invalid JPEG: $($photo.Name)" }
        } finally { $img.Dispose() }
    }
    New-Item -ItemType Directory -Path $backup | Out-Null
    foreach ($photo in $photos) { Copy-Item -LiteralPath (Join-Path $target $photo.Name) -Destination (Join-Path $backup $photo.Name) }
    try {
        foreach ($photo in $photos) { Copy-Item -LiteralPath (Join-Path $stage $photo.Name) -Destination (Join-Path $target $photo.Name) -Force }
    } catch {
        foreach ($photo in $photos) { Copy-Item -LiteralPath (Join-Path $backup $photo.Name) -Destination (Join-Path $target $photo.Name) -Force }
        throw
    }
    Write-Host "SUCCESS: 10 Welcome Page photos updated." -ForegroundColor Green
    Write-Host "Backup: $backup"
    Write-Host 'Refresh browser with Ctrl+F5. No build or migration needed.'
} finally {
    Remove-Item -LiteralPath $stage -Recurse -Force -ErrorAction SilentlyContinue
}
