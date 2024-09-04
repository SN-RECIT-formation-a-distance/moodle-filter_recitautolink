$from = "moodle-filter_recitautolink/src/*"
$to = "shared/recitfad3/filter/recitactivity/"

try {
    . ("..\sync\watcher.ps1")
}
catch {
    Write-Host "Error while loading sync.ps1 script." 
}