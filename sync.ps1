$from = "moodle-filter_recitautolink/src/*"
$to = "shared/recitfad/filter/recitactivity/"
$source = "./src";

try {
    . ("..\sync\watcher.ps1")
}
catch {
    Write-Host "Error while loading sync.ps1 script." 
}