# Запускать от имени Администратора!
# Регистрирует задачу Windows Task Scheduler для Laravel-планировщика.

$phpPath   = "C:\xampp\php\php.exe"
$workDir   = "C:\xampp\htdocs\laravel-project"
$taskName  = "Laravel Nobel Scheduler"

$action  = New-ScheduledTaskAction `
    -Execute $phpPath `
    -Argument "artisan schedule:run" `
    -WorkingDirectory $workDir

# Каждую минуту, бессрочно
$trigger = New-ScheduledTaskTrigger -RepetitionInterval (New-TimeSpan -Minutes 1) -Once -At "2000-01-01 00:00:00"

$settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 10) `
    -MultipleInstances IgnoreNew

Register-ScheduledTask `
    -TaskName  $taskName `
    -Action    $action `
    -Trigger   $trigger `
    -Settings  $settings `
    -RunLevel  Highest `
    -Force

Write-Host "Задача '$taskName' зарегистрирована." -ForegroundColor Green
Write-Host "ETL будет запускаться ежедневно в 02:00."
