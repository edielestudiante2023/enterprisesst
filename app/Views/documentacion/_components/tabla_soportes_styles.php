<?php
/**
 * Estilos CSS para el componente tabla_soportes.php
 * Tokens de diseno identicos a tabla_documentos_sst.php para homogeneidad visual
 * Se incluye automaticamente desde tabla_soportes.php
 */
?>
<style>
/* Card principal soportes */
.tabla-soportes-card {
    border-radius: 16px;
    overflow: hidden;
    border: none;
}

/* Headers con gradiente - variantes de color */
.header-soportes {
    background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #818cf8 100%);
    padding: 20px 24px;
    border: none;
}

.header-soportes-info {
    background: linear-gradient(135deg, #0e7490 0%, #06b6d4 50%, #22d3ee 100%);
    padding: 20px 24px;
    border: none;
}

.header-soportes-success {
    background: linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%);
    padding: 20px 24px;
    border: none;
}

.header-soportes-warning {
    background: linear-gradient(135deg, #92400e 0%, #d97706 50%, #f59e0b 100%);
    padding: 20px 24px;
    border: none;
}

.header-soportes-primary {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%);
    padding: 20px 24px;
    border: none;
}

.header-soportes-secondary {
    background: linear-gradient(135deg, #334155 0%, #475569 50%, #64748b 100%);
    padding: 20px 24px;
    border: none;
}

/* Icon wrapper (mismo token que tabla_documentos_sst) */
.tabla-soportes-card .icon-wrapper {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.tabla-soportes-card .header-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
}

.tabla-soportes-card .header-subtitle {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
    margin-top: 2px;
}

.tabla-soportes-card .stat-badge {
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: white;
    font-weight: 500;
}

/* Header de la tabla (mismo gradiente que tabla_documentos_sst) */
.tabla-soportes-moderna thead th {
    background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    padding: 14px 12px;
    border: none;
    white-space: nowrap;
}

/* Filas con animacion fadeInUp (mismo que tabla_documentos_sst) */
.tabla-soportes-moderna tbody tr {
    animation: soportesFadeInUp 0.4s ease forwards;
    opacity: 0;
    transition: all 0.2s;
}

.tabla-soportes-moderna tbody tr:nth-child(1) { animation-delay: 0.05s; }
.tabla-soportes-moderna tbody tr:nth-child(2) { animation-delay: 0.1s; }
.tabla-soportes-moderna tbody tr:nth-child(3) { animation-delay: 0.15s; }
.tabla-soportes-moderna tbody tr:nth-child(4) { animation-delay: 0.2s; }
.tabla-soportes-moderna tbody tr:nth-child(5) { animation-delay: 0.25s; }
.tabla-soportes-moderna tbody tr:nth-child(n+6) { animation-delay: 0.3s; }

@keyframes soportesFadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tabla-soportes-moderna tbody tr:hover {
    background: #f0f9ff;
}

.tabla-soportes-moderna tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

/* Codigo badge (mismo token que tabla_documentos_sst) */
.tabla-soportes-moderna .codigo-badge {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: monospace;
}

/* Nombre del soporte */
.tabla-soportes-moderna .nombre-soporte {
    font-weight: 500;
    color: #1e293b;
}

.tabla-soportes-moderna .obs-soporte {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 2px;
}

/* Badge de anio (mismo que tabla_documentos_sst) */
.tabla-soportes-moderna .anio-badge {
    background: #e2e8f0;
    color: #475569;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Fecha */
.tabla-soportes-moderna .fecha-cell {
    font-size: 0.85rem;
    color: #475569;
}

/* Badges de tipo modernos (enlace/archivo) */
.tipo-badge-enlace {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: white;
}

.tipo-badge-archivo {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
    color: white;
}

/* Botones de acciones (mismo estilo tabla_documentos_sst) */
.tabla-soportes-moderna .acciones-cell .btn {
    border-radius: 8px;
    margin: 0 2px;
}

/* Empty state (mismo patron que tabla_documentos_sst) */
.tabla-soportes-card .empty-state {
    text-align: center;
    padding: 60px 20px;
}

.tabla-soportes-card .empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #e2e8f0 0%, #f1f5f9 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.tabla-soportes-card .empty-icon i {
    font-size: 2.5rem;
    color: #94a3b8;
}

.tabla-soportes-card .empty-state h5 {
    color: #475569;
    margin-bottom: 8px;
}

.tabla-soportes-card .empty-state p {
    color: #94a3b8;
    max-width: 300px;
    margin: 0 auto;
}

/* Responsive */
@media (max-width: 768px) {
    .tabla-soportes-card .header-stats {
        display: none;
    }
    .tabla-soportes-moderna th,
    .tabla-soportes-moderna td {
        font-size: 0.8rem;
        padding: 10px 8px;
    }
}
</style>
