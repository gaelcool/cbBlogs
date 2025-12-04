# Production Readiness - CSS Font Hierarchy Updater
# This script updates all CSS files with the proper font hierarchy

$cssFiles = @(
    "account-inf.css", "democracy.css", "editor.css", "LP.css",
    "login.css", "read.css", "registrar.css", "reportstyle.css",
    "resources.css", "style.css", "updateAcc.css", "writemedia.css",
    "write.css", "us.css"
)

$cssDir = "c:\xampp\htdocs\cbGit\css"

# Font rules to add/update Currently unused for some reason.
$bodyFontRule = "font-family: 'Fira Sans', sans-serif;"
$headerFontRule = "font-family: 'Times New Roman', serif;"
$subtitleFontRule = "font-family: 'Fira Code', monospace; font-weight: bold; font-size: 14.5px;"

foreach ($file in $cssFiles) {
    $filePath = Join-Path $cssDir $file
    
    if (Test-Path $filePath) {
        $content = Get-Content $filePath -Raw
        
        # Replace existing font-family in body/* selector
        $content = $content -replace "(body[^{]*\{[^}]*font-family:\s*)[^;]+;", "`$1 'Fira Sans', sans-serif; font-size: 14px;"
        $content = $content -replace "(\*[^{]*\{[^}]*font-family:\s*)[^;]+;", "`$1 'Fira Sans', sans-serif; font-size: 14px;"
        
        # Add header font rule if h1, h2, h3 etc. are found
        if ($content -match "h[1-6]") {
            # Check if we already have a global h1-h6 rule
            if ($content -notmatch "h1,\s*h2,\s*h3") {
                # Add after :root or at beginning of file
                $headerRule = "`n`nh1, h2, h3, h4, h5, h6 {`n    font-family: 'Times New Roman', serif;`n}`n"
                
                if ($content -match ":root[^}]*\}") {
                    $content = $content -replace "(:root[^}]*\})", "`$1$headerRule"
                } else {
                    $content = $headerRule + $content
                }
            }
        }
        
        Set-Content -Path $filePath -Value $content -NoNewline
        Write-Host "Updated: $file" -ForegroundColor Green
    } else {
        Write-Host "Not found: $file" -ForegroundColor Yellow
    }
}

Write-Host "`n Font hierarchy updated in all CSS files!" -ForegroundColor Cyan
