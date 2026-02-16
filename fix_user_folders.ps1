#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Repara carpetas de usuario que apuntan a OneDrive inexistente
    y mueve Descargas al disco D:\

.DESCRIPTION
    - Corrige Desktop, Documents, Pictures, etc. que apunten a OneDrive
    - Crea D:\Descargas y la configura como carpeta de Downloads por defecto
    - Reinicia Explorer al final
#>

[CmdletBinding(SupportsShouldProcess)]
param()

$ErrorActionPreference = 'Continue'
$UserName = 'elipt'
$UserProfile = "C:\Users\$UserName"

# ============================================================
# Carpetas de usuario (Shell Folders)
# Registro: HKCU\Software\Microsoft\Windows\CurrentVersion\Explorer\User Shell Folders
# ============================================================
$ShellFoldersPath = 'HKCU:\Software\Microsoft\Windows\CurrentVersion\Explorer\User Shell Folders'
$ShellFoldersLegacy = 'HKCU:\Software\Microsoft\Windows\CurrentVersion\Explorer\Shell Folders'

# Mapa de carpetas: nombre de registro => ruta local correcta
# Downloads, Pictures, Screenshots, Videos van a D:\
$FolderMap = @{
    'Desktop'           = "$UserProfile\Desktop"
    '{374DE290-123F-4565-9164-39C4925E467B}' = 'D:\Descargas'            # Downloads
    'Personal'          = "$UserProfile\Documents"                        # Documents
    'My Pictures'       = 'D:\Imagenes'                                   # Pictures
    'My Music'          = "$UserProfile\Music"
    'My Video'          = 'D:\Videos'                                     # Videos
    '{F42EE2D3-909F-4907-8871-4C22FC0BF756}' = "$UserProfile\Documents"  # Documents (alt GUID)
    '{0DDD015D-B06C-45D5-8C4C-F59713854639}' = 'D:\Imagenes'            # Pictures (alt GUID)
    '{35286A68-3C57-41A1-BBB1-0EAE73D76C95}' = 'D:\Videos'              # Videos (alt GUID)
    '{A0C69A99-21C8-4671-8703-7934162FCF1D}' = "$UserProfile\Music"      # Music (alt GUID)
    '{BFB9D5E0-C6A9-404C-B2B2-AE6DB6AF4968}' = "$UserProfile\Links"     # Links
    '{B7BEDE81-DF94-4682-A7D8-57A52620B86F}' = 'D:\Imagenes\Screenshots' # Screenshots
}

Write-Host ''
Write-Host '====================================================' -ForegroundColor Cyan
Write-Host '  Fix User Folders - Reparar carpetas de usuario' -ForegroundColor Cyan
Write-Host '====================================================' -ForegroundColor Cyan
Write-Host ''

# ---- Paso 1: Backup del registro ----
Write-Host '[1] Exportando backup de Shell Folders...' -ForegroundColor Yellow
$backupDir = 'D:\logs\onedrive_cleanup'
if (-not (Test-Path $backupDir)) { New-Item -Path $backupDir -ItemType Directory -Force | Out-Null }
$ts = Get-Date -Format 'yyyyMMdd_HHmm'
& reg.exe export 'HKCU\Software\Microsoft\Windows\CurrentVersion\Explorer\User Shell Folders' "$backupDir\backup_UserShellFolders_$ts.reg" /y 2>$null
& reg.exe export 'HKCU\Software\Microsoft\Windows\CurrentVersion\Explorer\Shell Folders' "$backupDir\backup_ShellFolders_$ts.reg" /y 2>$null
Write-Host "  Backups en: $backupDir" -ForegroundColor Green
Write-Host ''

# ---- Paso 2: Crear carpetas en D:\ si no existen ----
Write-Host '[2] Creando carpetas en D:\...' -ForegroundColor Yellow
$dFolders = @('D:\Descargas', 'D:\Imagenes', 'D:\Imagenes\Screenshots', 'D:\Videos')
foreach ($df in $dFolders) {
    if (-not (Test-Path $df)) {
        if ($PSCmdlet.ShouldProcess($df, 'Crear directorio')) {
            New-Item -Path $df -ItemType Directory -Force | Out-Null
            Write-Host "  $df creada." -ForegroundColor Green
        }
    } else {
        Write-Host "  $df ya existe." -ForegroundColor Green
    }
}
Write-Host ''

# ---- Paso 3: Leer valores actuales y corregir ----
Write-Host '[3] Escaneando carpetas de usuario...' -ForegroundColor Yellow
Write-Host ''

$currentProps = Get-ItemProperty -Path $ShellFoldersPath -ErrorAction SilentlyContinue
$fixed = 0
$alreadyOk = 0

foreach ($entry in $FolderMap.GetEnumerator()) {
    $regName = $entry.Key
    $targetPath = $entry.Value
    $currentValue = $currentProps.$regName

    # Nombre legible
    $friendlyName = switch ($regName) {
        'Desktop'           { 'Escritorio' }
        '{374DE290-123F-4565-9164-39C4925E467B}' { 'Descargas (Downloads)' }
        'Personal'          { 'Documentos' }
        'My Pictures'       { 'Imagenes' }
        'My Music'          { 'Musica' }
        'My Video'          { 'Videos' }
        '{F42EE2D3-909F-4907-8871-4C22FC0BF756}' { 'Documentos (GUID)' }
        '{0DDD015D-B06C-45D5-8C4C-F59713854639}' { 'Imagenes (GUID)' }
        '{35286A68-3C57-41A1-BBB1-0EAE73D76C95}' { 'Videos (GUID)' }
        '{A0C69A99-21C8-4671-8703-7934162FCF1D}' { 'Musica (GUID)' }
        '{BFB9D5E0-C6A9-404C-B2B2-AE6DB6AF4968}' { 'Links' }
        '{B7BEDE81-DF94-4682-A7D8-57A52620B86F}' { 'Capturas de pantalla' }
        default { $regName }
    }

    $needsFix = $false

    if ($null -eq $currentValue) {
        Write-Host "  $friendlyName : (no configurado) -> $targetPath" -ForegroundColor Yellow
        $needsFix = $true
    }
    elseif ($currentValue -match 'OneDrive') {
        Write-Host "  $friendlyName : $currentValue" -ForegroundColor Red -NoNewline
        Write-Host " -> $targetPath" -ForegroundColor Green
        $needsFix = $true
    }
    elseif ($currentValue -ne $targetPath -and $targetPath -match '^D:\\') {
        # Carpetas que van a D:\ - forzar aunque no apunten a OneDrive
        Write-Host "  $friendlyName : $currentValue" -ForegroundColor Yellow -NoNewline
        Write-Host " -> $targetPath" -ForegroundColor Green
        $needsFix = $true
    }
    else {
        Write-Host "  $friendlyName : $currentValue" -ForegroundColor DarkGray -NoNewline
        Write-Host ' [OK]' -ForegroundColor Green
        $alreadyOk++
    }

    if ($needsFix) {
        # Crear carpeta destino si no existe
        if (-not (Test-Path $targetPath)) {
            if ($PSCmdlet.ShouldProcess($targetPath, 'Crear directorio')) {
                New-Item -Path $targetPath -ItemType Directory -Force | Out-Null
            }
        }

        if ($PSCmdlet.ShouldProcess("$regName = $targetPath", 'Actualizar User Shell Folders')) {
            # User Shell Folders (con variables expandibles)
            Set-ItemProperty -Path $ShellFoldersPath -Name $regName -Value $targetPath -Type ExpandString -Force
            # Shell Folders (legacy, valor literal)
            if (Test-Path $ShellFoldersLegacy) {
                Set-ItemProperty -Path $ShellFoldersLegacy -Name $regName -Value $targetPath -Type String -Force -ErrorAction SilentlyContinue
            }
            $fixed++
        }
    }
}

Write-Host ''

# ---- Paso 4: Configurar D:\Descargas via KnownFolder API ----
Write-Host '[4] Registrando D:\Descargas como Known Folder...' -ForegroundColor Yellow

# Metodo adicional: actualizar via SHSetKnownFolderPath no es accesible directo desde PS,
# pero el registro es suficiente. Notificar al shell del cambio.
Add-Type -TypeDefinition @"
using System;
using System.Runtime.InteropServices;

public class ShellNotify {
    [DllImport("shell32.dll")]
    public static extern void SHChangeNotify(int wEventId, int uFlags, IntPtr dwItem1, IntPtr dwItem2);

    public const int SHCNE_ASSOCCHANGED = 0x08000000;
    public const int SHCNF_IDLIST = 0x0000;

    public static void Refresh() {
        SHChangeNotify(SHCNE_ASSOCCHANGED, SHCNF_IDLIST, IntPtr.Zero, IntPtr.Zero);
    }
}
"@ -ErrorAction SilentlyContinue

if ($PSCmdlet.ShouldProcess('Shell', 'Notificar cambio de carpetas')) {
    [ShellNotify]::Refresh()
    Write-Host '  Shell notificado del cambio.' -ForegroundColor Green
}
Write-Host ''

# ---- Paso 5: Reiniciar Explorer ----
Write-Host '[5] Reiniciando Explorer...' -ForegroundColor Yellow
if ($PSCmdlet.ShouldProcess('explorer.exe', 'Reiniciar')) {
    Stop-Process -Name 'explorer' -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
    Start-Process 'explorer.exe'
    Write-Host '  Explorer reiniciado.' -ForegroundColor Green
}
Write-Host ''

# ---- Resumen ----
Write-Host '====================================================' -ForegroundColor Cyan
Write-Host '  RESUMEN' -ForegroundColor Cyan
Write-Host '====================================================' -ForegroundColor Cyan
Write-Host "  Carpetas corregidas : $fixed" -ForegroundColor $(if ($fixed -gt 0) { 'Green' } else { 'White' })
Write-Host "  Carpetas ya correctas: $alreadyOk" -ForegroundColor White
Write-Host "  Descargas ahora en  : D:\Descargas" -ForegroundColor Green
Write-Host "  Imagenes ahora en   : D:\Imagenes" -ForegroundColor Green
Write-Host "  Screenshots ahora en: D:\Imagenes\Screenshots" -ForegroundColor Green
Write-Host "  Videos ahora en     : D:\Videos" -ForegroundColor Green
Write-Host ''
Write-Host '  Si el error de Escritorio persiste, cierra sesion' -ForegroundColor Yellow
Write-Host '  y vuelve a iniciar para que Windows aplique los cambios.' -ForegroundColor Yellow
Write-Host ''
