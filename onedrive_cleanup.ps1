#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Elimina TODO rastro de OneDrive del perfil de usuario en Windows 11.

.DESCRIPTION
    - Desvincula todas las cuentas OneDrive (Personal, Business, SENA, etc.)
    - Limpia entradas del Explorador de Windows (panel izquierdo)
    - Exporta backups .reg antes de cualquier cambio
    - Crea punto de restauracion del sistema
    - Log detallado en D:\logs\onedrive_cleanup\

.PARAMETER DeleteLocalFolders
    Si se especifica, elimina tambien las carpetas C:\Users\elipt\OneDrive*

.PARAMETER WhatIf
    Simulacion: muestra que haria sin ejecutar cambios.

.EXAMPLE
    .\onedrive_cleanup.ps1 -WhatIf
    .\onedrive_cleanup.ps1
    .\onedrive_cleanup.ps1 -DeleteLocalFolders
#>

[CmdletBinding(SupportsShouldProcess)]
param(
    [switch]$DeleteLocalFolders
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Continue'

# ============================================================
# CONFIGURACION
# ============================================================
$UserName       = 'elipt'
$UserProfile    = "C:\Users\$UserName"
$Timestamp      = Get-Date -Format 'yyyyMMdd_HHmm'
$LogRoot        = 'D:\logs\onedrive_cleanup'
$BackupDir      = Join-Path $LogRoot "backup_$Timestamp"
$LogFile        = Join-Path $LogRoot "onedrive_cleanup_$Timestamp.log"

# Claves de registro a procesar
$RegOneDrive         = 'HKCU:\Software\Microsoft\OneDrive'
$RegOneDriveAccounts = 'HKCU:\Software\Microsoft\OneDrive\Accounts'
$RegDesktopNS        = 'HKCU:\Software\Microsoft\Windows\CurrentVersion\Explorer\Desktop\NameSpace'
$RegMyComputerNS     = 'HKCU:\Software\Microsoft\Windows\CurrentVersion\Explorer\MyComputer\NameSpace'

# Claves CLSID conocidas de OneDrive en el Explorador
$OneDriveCLSIDs = @(
    '{018D5C66-4533-4307-9B53-224DE2ED1FE6}' # OneDrive Personal
    '{04271989-C4D2-4ECC-B944-2BE9B9A37299}' # OneDrive (generico)
)

# Contadores para resumen
$Summary = [ordered]@{
    ClavesRegistroEncontradas = 0
    ClavesRegistroEliminadas  = 0
    EntradasExplorador        = 0
    EntradasExploradorLimpias  = 0
    CarpetasEncontradas       = 0
    CarpetasEliminadas        = 0
    Errores                   = 0
}

# ============================================================
# FUNCIONES
# ============================================================
function Write-Log {
    param(
        [string]$Message,
        [ValidateSet('INFO','WARN','ERROR','OK','SECTION')]
        [string]$Level = 'INFO'
    )
    $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    $prefix = switch ($Level) {
        'INFO'    { '[INFO]   ' }
        'WARN'    { '[WARN]   ' }
        'ERROR'   { '[ERROR]  ' }
        'OK'      { '[OK]     ' }
        'SECTION' { '[======] ' }
    }
    $line = "$ts $prefix $Message"

    # Escribir al archivo de log si existe
    if (Test-Path (Split-Path $LogFile -Parent)) {
        Add-Content -Path $LogFile -Value $line -Encoding UTF8
    }

    # Escribir a consola con colores
    $color = switch ($Level) {
        'INFO'    { 'White' }
        'WARN'    { 'Yellow' }
        'ERROR'   { 'Red' }
        'OK'      { 'Green' }
        'SECTION' { 'Cyan' }
    }
    Write-Host $line -ForegroundColor $color
}

function Test-IsOneDriveEntry {
    <# Determina si una subclave de NameSpace corresponde a OneDrive #>
    param([string]$RegistryPath)

    try {
        # Verificar CLSID conocido
        $clsid = Split-Path $RegistryPath -Leaf
        if ($OneDriveCLSIDs -contains $clsid) { return $true }

        # Verificar valor por defecto
        $defaultVal = (Get-ItemProperty -Path $RegistryPath -Name '(Default)' -ErrorAction SilentlyContinue).'(Default)'
        if ($defaultVal -and $defaultVal -match 'OneDrive') { return $true }

        # Verificar InProcServer32 en HKCR
        $hkcrPath = "Registry::HKEY_CLASSES_ROOT\CLSID\$clsid"
        if (Test-Path $hkcrPath) {
            $desc = (Get-ItemProperty -Path $hkcrPath -Name '(Default)' -ErrorAction SilentlyContinue).'(Default)'
            if ($desc -and $desc -match 'OneDrive') { return $true }
        }
    }
    catch {
        # Silenciar - no es OneDrive
    }
    return $false
}

function Export-RegistryBackup {
    param(
        [string]$HKCUPath,
        [string]$FileName
    )
    # Convertir HKCU:\ a HKCU\ para reg.exe
    $regExePath = $HKCUPath -replace '^HKCU:\\', 'HKCU\'
    $outFile = Join-Path $BackupDir "$FileName.reg"

    if (Test-Path $HKCUPath) {
        $result = & reg.exe export $regExePath $outFile /y 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Log "Backup exportado: $outFile" -Level OK
        } else {
            Write-Log "No se pudo exportar $regExePath : $result" -Level WARN
        }
    } else {
        Write-Log "Clave no existe, nada que exportar: $HKCUPath" -Level WARN
    }
}

# ============================================================
# PASO 0: VERIFICAR ADMINISTRADOR
# ============================================================
$identity  = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($identity)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host ''
    Write-Host '========================================================' -ForegroundColor Red
    Write-Host '  ERROR: Este script requiere privilegios de Administrador.' -ForegroundColor Red
    Write-Host '  Abre PowerShell como Administrador y vuelve a ejecutar.' -ForegroundColor Red
    Write-Host '========================================================' -ForegroundColor Red
    Write-Host ''
    exit 1
}

# ============================================================
# PASO 1: CREAR DIRECTORIOS DE LOG Y BACKUP
# ============================================================
if (-not (Test-Path $LogRoot))  { New-Item -Path $LogRoot  -ItemType Directory -Force | Out-Null }
if (-not (Test-Path $BackupDir)){ New-Item -Path $BackupDir -ItemType Directory -Force | Out-Null }

# Iniciar log
Set-Content -Path $LogFile -Value '' -Encoding UTF8
Write-Log '============================================================' -Level SECTION
Write-Log '  OneDrive Cleanup Script - Inicio' -Level SECTION
Write-Log "  Timestamp : $Timestamp" -Level SECTION
Write-Log "  Modo      : $(if ($WhatIfPreference) { 'SIMULACION (WhatIf)' } else { 'EJECUCION REAL' })" -Level SECTION
Write-Log "  DeleteLocalFolders: $DeleteLocalFolders" -Level SECTION
Write-Log '============================================================' -Level SECTION
Write-Log ''

# ============================================================
# PASO 2: PUNTO DE RESTAURACION + BACKUPS .REG
# ============================================================
Write-Log '--- Paso 2: Respaldos ---' -Level SECTION

# Punto de restauracion
Write-Log 'Intentando crear punto de restauracion del sistema...'
try {
    if ($PSCmdlet.ShouldProcess('Sistema', 'Crear punto de restauracion')) {
        # Habilitar restauracion en C: si no esta activo
        Enable-ComputerRestore -Drive 'C:\' -ErrorAction SilentlyContinue
        Checkpoint-Computer -Description "Pre-OneDrive-Cleanup-$Timestamp" -RestorePointType MODIFY_SETTINGS -ErrorAction Stop
        Write-Log 'Punto de restauracion creado correctamente.' -Level OK
    } else {
        Write-Log 'WhatIf: Se crearia punto de restauracion.' -Level WARN
    }
}
catch {
    Write-Log "No se pudo crear punto de restauracion: $($_.Exception.Message)" -Level WARN
    Write-Log 'Continuando con backups de registro...' -Level INFO
}

# Exportar backups .reg
Write-Log 'Exportando backups de registro...'
Export-RegistryBackup -HKCUPath $RegOneDrive      -FileName 'backup_OneDrive'
Export-RegistryBackup -HKCUPath $RegDesktopNS      -FileName 'backup_DesktopNameSpace'
Export-RegistryBackup -HKCUPath $RegMyComputerNS   -FileName 'backup_MyComputerNameSpace'

Write-Log ''

# ============================================================
# PASO 3: DETENER PROCESOS DE ONEDRIVE
# ============================================================
Write-Log '--- Paso 3: Detener procesos OneDrive ---' -Level SECTION

$odProcesses = Get-Process -Name 'OneDrive' -ErrorAction SilentlyContinue
if ($odProcesses) {
    Write-Log "Encontrados $($odProcesses.Count) proceso(s) OneDrive corriendo."
    foreach ($proc in $odProcesses) {
        Write-Log "  PID $($proc.Id) - $($proc.ProcessName) [$($proc.Path)]"
        if ($PSCmdlet.ShouldProcess("OneDrive PID $($proc.Id)", 'Detener proceso')) {
            try {
                $proc | Stop-Process -Force -ErrorAction Stop
                Write-Log "  Detenido PID $($proc.Id)" -Level OK
            }
            catch {
                Write-Log "  No se pudo detener PID $($proc.Id): $($_.Exception.Message)" -Level ERROR
                $Summary.Errores++
            }
        } else {
            Write-Log "  WhatIf: Se detendria PID $($proc.Id)" -Level WARN
        }
    }
} else {
    Write-Log 'No hay procesos OneDrive corriendo.' -Level OK
}

# Tambien detener OneDriveSetup si existe
$odSetup = Get-Process -Name 'OneDriveSetup' -ErrorAction SilentlyContinue
if ($odSetup) {
    foreach ($proc in $odSetup) {
        if ($PSCmdlet.ShouldProcess("OneDriveSetup PID $($proc.Id)", 'Detener proceso')) {
            $proc | Stop-Process -Force -ErrorAction SilentlyContinue
            Write-Log "  Detenido OneDriveSetup PID $($proc.Id)" -Level OK
        }
    }
}

Write-Log ''

# ============================================================
# PASO 4: ELIMINAR CUENTAS EN REGISTRO
# ============================================================
Write-Log '--- Paso 4: Eliminar cuentas OneDrive del registro ---' -Level SECTION

if (Test-Path $RegOneDriveAccounts) {
    $accounts = Get-ChildItem -Path $RegOneDriveAccounts -ErrorAction SilentlyContinue
    if ($accounts) {
        Write-Log "Encontradas $($accounts.Count) cuenta(s) en $RegOneDriveAccounts :"
        foreach ($acct in $accounts) {
            $acctName = $acct.PSChildName
            $acctPath = $acct.PSPath
            $Summary.ClavesRegistroEncontradas++
            Write-Log "  Cuenta: $acctName"

            if ($PSCmdlet.ShouldProcess($acctPath, 'Eliminar subclave de cuenta')) {
                try {
                    Remove-Item -Path $acctPath -Recurse -Force -ErrorAction Stop
                    Write-Log "  Eliminada: $acctName" -Level OK
                    $Summary.ClavesRegistroEliminadas++
                }
                catch {
                    Write-Log "  Error eliminando $acctName : $($_.Exception.Message)" -Level ERROR
                    $Summary.Errores++
                }
            } else {
                Write-Log "  WhatIf: Se eliminaria $acctName" -Level WARN
            }
        }
    } else {
        Write-Log 'No se encontraron subcuentas en Accounts.' -Level INFO
    }
} else {
    Write-Log "La clave $RegOneDriveAccounts no existe." -Level WARN
}

# Eliminar tambien la clave raiz de OneDrive para limpiar configuracion residual
if (Test-Path $RegOneDrive) {
    $Summary.ClavesRegistroEncontradas++
    Write-Log "Clave raiz encontrada: $RegOneDrive"
    if ($PSCmdlet.ShouldProcess($RegOneDrive, 'Eliminar clave raiz OneDrive')) {
        try {
            Remove-Item -Path $RegOneDrive -Recurse -Force -ErrorAction Stop
            Write-Log "Eliminada clave raiz: $RegOneDrive" -Level OK
            $Summary.ClavesRegistroEliminadas++
        }
        catch {
            Write-Log "Error eliminando clave raiz: $($_.Exception.Message)" -Level ERROR
            $Summary.Errores++
        }
    } else {
        Write-Log "WhatIf: Se eliminaria clave raiz $RegOneDrive" -Level WARN
    }
}

Write-Log ''

# ============================================================
# PASO 5: LIMPIAR ENTRADAS DEL EXPLORADOR (NameSpace)
# ============================================================
Write-Log '--- Paso 5: Limpiar entradas del Explorador ---' -Level SECTION

$namespacePaths = @($RegDesktopNS, $RegMyComputerNS)

foreach ($nsPath in $namespacePaths) {
    Write-Log "Escaneando: $nsPath"
    if (-not (Test-Path $nsPath)) {
        Write-Log "  No existe: $nsPath" -Level WARN
        continue
    }

    $subkeys = Get-ChildItem -Path $nsPath -ErrorAction SilentlyContinue
    foreach ($sk in $subkeys) {
        $fullPath = $sk.PSPath
        if (Test-IsOneDriveEntry -RegistryPath $fullPath) {
            $Summary.EntradasExplorador++
            $defaultVal = (Get-ItemProperty -Path $fullPath -Name '(Default)' -ErrorAction SilentlyContinue).'(Default)'
            Write-Log "  OneDrive encontrado: $($sk.PSChildName) = '$defaultVal'"

            if ($PSCmdlet.ShouldProcess($fullPath, 'Eliminar entrada del Explorador')) {
                try {
                    Remove-Item -Path $fullPath -Recurse -Force -ErrorAction Stop
                    Write-Log "  Eliminada: $($sk.PSChildName)" -Level OK
                    $Summary.EntradasExploradorLimpias++
                }
                catch {
                    Write-Log "  Error: $($_.Exception.Message)" -Level ERROR
                    $Summary.Errores++
                }
            } else {
                Write-Log "  WhatIf: Se eliminaria $($sk.PSChildName)" -Level WARN
            }
        }
    }
}

# Limpiar tambien HKLM NameSpace (requiere admin)
$hklmNamespaces = @(
    'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Explorer\Desktop\NameSpace'
    'HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Explorer\Desktop\NameSpace'
)

foreach ($nsPath in $hklmNamespaces) {
    Write-Log "Escaneando (HKLM): $nsPath"
    if (-not (Test-Path $nsPath)) {
        Write-Log "  No existe: $nsPath" -Level WARN
        continue
    }

    $subkeys = Get-ChildItem -Path $nsPath -ErrorAction SilentlyContinue
    foreach ($sk in $subkeys) {
        $fullPath = $sk.PSPath
        if (Test-IsOneDriveEntry -RegistryPath $fullPath) {
            $Summary.EntradasExplorador++
            Write-Log "  OneDrive encontrado (HKLM): $($sk.PSChildName)"

            if ($PSCmdlet.ShouldProcess($fullPath, 'Eliminar entrada HKLM del Explorador')) {
                try {
                    Remove-Item -Path $fullPath -Recurse -Force -ErrorAction Stop
                    Write-Log "  Eliminada (HKLM): $($sk.PSChildName)" -Level OK
                    $Summary.EntradasExploradorLimpias++
                }
                catch {
                    Write-Log "  Error: $($_.Exception.Message)" -Level ERROR
                    $Summary.Errores++
                }
            } else {
                Write-Log "  WhatIf: Se eliminaria $($sk.PSChildName)" -Level WARN
            }
        }
    }
}

# Ocultar OneDrive del panel de navegacion via CLSID
$shellFolderPaths = @(
    'HKCU:\Software\Classes\CLSID\{018D5C66-4533-4307-9B53-224DE2ED1FE6}'
    'HKLM:\SOFTWARE\Policies\Microsoft\Windows\OneDrive'
)

Write-Log 'Configurando ocultamiento de OneDrive en navegacion...'
# Deshabilitar icono de OneDrive via CLSID
$clsidPath = 'HKCU:\Software\Classes\CLSID\{018D5C66-4533-4307-9B53-224DE2ED1FE6}'
if (Test-Path $clsidPath) {
    if ($PSCmdlet.ShouldProcess($clsidPath, 'Configurar System.IsPinnedToNameSpaceTree = 0')) {
        try {
            Set-ItemProperty -Path $clsidPath -Name 'System.IsPinnedToNameSpaceTree' -Value 0 -Type DWord -Force
            Write-Log "Desanclado OneDrive del Explorador via CLSID" -Level OK
        }
        catch {
            Write-Log "Error configurando CLSID: $($_.Exception.Message)" -Level WARN
        }
    }
}

# Tambien el WOW6432Node para 64-bit
$clsid64Path = 'HKCU:\Software\Classes\Wow6432Node\CLSID\{018D5C66-4533-4307-9B53-224DE2ED1FE6}'
if (Test-Path $clsid64Path) {
    if ($PSCmdlet.ShouldProcess($clsid64Path, 'Configurar System.IsPinnedToNameSpaceTree = 0')) {
        try {
            Set-ItemProperty -Path $clsid64Path -Name 'System.IsPinnedToNameSpaceTree' -Value 0 -Type DWord -Force
            Write-Log "Desanclado OneDrive (64-bit) del Explorador via CLSID" -Level OK
        }
        catch {
            Write-Log "Error: $($_.Exception.Message)" -Level WARN
        }
    }
}

# Politica para deshabilitar OneDrive
$policyPath = 'HKLM:\SOFTWARE\Policies\Microsoft\Windows\OneDrive'
if ($PSCmdlet.ShouldProcess($policyPath, 'Crear politica DisableFileSyncNGSC')) {
    try {
        if (-not (Test-Path $policyPath)) {
            New-Item -Path $policyPath -Force | Out-Null
        }
        Set-ItemProperty -Path $policyPath -Name 'DisableFileSyncNGSC' -Value 1 -Type DWord -Force
        Write-Log "Politica DisableFileSyncNGSC activada" -Level OK
    }
    catch {
        Write-Log "Error creando politica: $($_.Exception.Message)" -Level WARN
    }
}

# Deshabilitar autostart de OneDrive
$runPath = 'HKCU:\Software\Microsoft\Windows\CurrentVersion\Run'
if (Test-Path $runPath) {
    $runProps = Get-ItemProperty -Path $runPath -ErrorAction SilentlyContinue
    $oneDriveRun = $runProps.PSObject.Properties | Where-Object { $_.Name -match 'OneDrive' }
    foreach ($entry in $oneDriveRun) {
        if ($PSCmdlet.ShouldProcess("$runPath\$($entry.Name)", 'Eliminar autostart OneDrive')) {
            try {
                Remove-ItemProperty -Path $runPath -Name $entry.Name -Force -ErrorAction Stop
                Write-Log "Eliminado autostart: $($entry.Name)" -Level OK
            }
            catch {
                Write-Log "Error eliminando autostart: $($_.Exception.Message)" -Level WARN
            }
        }
    }
}

Write-Log ''

# ============================================================
# PASO 6: ELIMINAR CARPETAS LOCALES (OPCIONAL)
# ============================================================
Write-Log '--- Paso 6: Carpetas locales OneDrive ---' -Level SECTION

$odFolders = Get-ChildItem -Path $UserProfile -Directory -Filter 'OneDrive*' -ErrorAction SilentlyContinue
if ($odFolders) {
    Write-Log "Encontradas $($odFolders.Count) carpeta(s) OneDrive en $UserProfile :"
    foreach ($folder in $odFolders) {
        $Summary.CarpetasEncontradas++
        $size = (Get-ChildItem -Path $folder.FullName -Recurse -File -ErrorAction SilentlyContinue |
                 Measure-Object -Property Length -Sum).Sum
        $sizeMB = [math]::Round($size / 1MB, 2)
        Write-Log "  $($folder.Name) - $sizeMB MB"

        if ($DeleteLocalFolders) {
            if ($PSCmdlet.ShouldProcess($folder.FullName, 'Eliminar carpeta local')) {
                try {
                    Remove-Item -Path $folder.FullName -Recurse -Force -ErrorAction Stop
                    Write-Log "  Eliminada: $($folder.Name)" -Level OK
                    $Summary.CarpetasEliminadas++
                }
                catch {
                    Write-Log "  No se pudo eliminar $($folder.Name): $($_.Exception.Message)" -Level ERROR
                    Write-Log "  (Puede estar en uso o sin permisos suficientes)" -Level WARN
                    $Summary.Errores++
                }
            } else {
                Write-Log "  WhatIf: Se eliminaria $($folder.Name)" -Level WARN
            }
        } else {
            Write-Log "  (No se elimina - use -DeleteLocalFolders para borrar)" -Level INFO
        }
    }
} else {
    Write-Log "No se encontraron carpetas OneDrive* en $UserProfile" -Level OK
}

Write-Log ''

# ============================================================
# PASO 7: REINICIAR EXPLORADOR
# ============================================================
Write-Log '--- Paso 7: Reiniciar Explorador de Windows ---' -Level SECTION

if ($PSCmdlet.ShouldProcess('explorer.exe', 'Reiniciar Explorador de Windows')) {
    Write-Log 'Deteniendo explorer.exe...'
    Stop-Process -Name 'explorer' -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
    Start-Process 'explorer.exe'
    Write-Log 'Explorador reiniciado.' -Level OK
} else {
    Write-Log 'WhatIf: Se reiniciaria el Explorador.' -Level WARN
}

Write-Log ''

# ============================================================
# RESUMEN FINAL
# ============================================================
Write-Log '============================================================' -Level SECTION
Write-Log '  RESUMEN FINAL' -Level SECTION
Write-Log '============================================================' -Level SECTION
Write-Log "  Modo                          : $(if ($WhatIfPreference) { 'SIMULACION' } else { 'EJECUCION REAL' })"
Write-Log "  Claves registro encontradas   : $($Summary.ClavesRegistroEncontradas)"
Write-Log "  Claves registro eliminadas    : $($Summary.ClavesRegistroEliminadas)"
Write-Log "  Entradas Explorador encontradas: $($Summary.EntradasExplorador)"
Write-Log "  Entradas Explorador limpiadas : $($Summary.EntradasExploradorLimpias)"
Write-Log "  Carpetas OneDrive encontradas : $($Summary.CarpetasEncontradas)"
Write-Log "  Carpetas eliminadas           : $($Summary.CarpetasEliminadas)"
Write-Log "  Errores                       : $($Summary.Errores)"
Write-Log ''
Write-Log "  Backup guardado en   : $BackupDir"
Write-Log "  Log guardado en      : $LogFile"
Write-Log ''

# Verificacion post-limpieza
Write-Log '--- Verificacion post-limpieza ---' -Level SECTION
$remainingAccounts = $null
if (Test-Path $RegOneDriveAccounts) {
    $remainingAccounts = Get-ChildItem -Path $RegOneDriveAccounts -ErrorAction SilentlyContinue
}
if ($remainingAccounts) {
    Write-Log "  ATENCION: Aun quedan $($remainingAccounts.Count) cuenta(s) en el registro:" -Level WARN
    foreach ($r in $remainingAccounts) {
        Write-Log "    - $($r.PSChildName)" -Level WARN
    }
} else {
    Write-Log '  No quedan cuentas OneDrive en el registro.' -Level OK
}

$remainingFolders = Get-ChildItem -Path $UserProfile -Directory -Filter 'OneDrive*' -ErrorAction SilentlyContinue
if ($remainingFolders) {
    Write-Log "  Carpetas OneDrive* aun presentes en disco:" -Level WARN
    foreach ($f in $remainingFolders) {
        Write-Log "    - $($f.FullName)" -Level WARN
    }
} else {
    Write-Log '  No quedan carpetas OneDrive en disco.' -Level OK
}

$remainingExplorer = @()
foreach ($nsPath in @($RegDesktopNS, $RegMyComputerNS)) {
    if (Test-Path $nsPath) {
        $subkeys = Get-ChildItem -Path $nsPath -ErrorAction SilentlyContinue
        foreach ($sk in $subkeys) {
            if (Test-IsOneDriveEntry -RegistryPath $sk.PSPath) {
                $remainingExplorer += $sk.PSChildName
            }
        }
    }
}
if ($remainingExplorer.Count -gt 0) {
    Write-Log "  ATENCION: Aun quedan entradas OneDrive en el Explorador:" -Level WARN
    foreach ($e in $remainingExplorer) {
        Write-Log "    - $e" -Level WARN
    }
} else {
    Write-Log '  No quedan entradas OneDrive en el Explorador.' -Level OK
}

Write-Log ''
Write-Log '============================================================' -Level SECTION
Write-Log '  Script finalizado.' -Level SECTION
Write-Log '============================================================' -Level SECTION
