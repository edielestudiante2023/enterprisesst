<style>
    .breadcrumb-item a { text-decoration: none; }
    .doc-card { transition: transform 0.2s; }
    .doc-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .folder-card { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); }
    .estado-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }
    /* Badges de estado IA */
    .estado-ia-pendiente { background-color: #6c757d; color: white; }
    .estado-ia-creado { background-color: #ffc107; color: #212529; }
    .estado-ia-aprobado { background-color: #198754; color: white; }

    /* Stats badges en carpetas */
    .folder-stats {
        display: flex;
        gap: 4px;
        justify-content: center;
        margin-top: 8px;
    }
    .folder-stats .badge {
        font-size: 0.65rem;
        padding: 2px 6px;
    }

    /* Indicador visual en tarjeta de documento */
    .doc-estado-indicator {
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 4px 0 0 4px;
    }
    .doc-estado-indicator.pendiente { background-color: #6c757d; }
    .doc-estado-indicator.creado { background-color: #ffc107; }
    .doc-estado-indicator.aprobado { background-color: #198754; }

    .doc-row { position: relative; }
    .doc-row td:first-child { padding-left: 12px; }

    /* Panel de Fases */
    .fases-panel {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }
    .fases-titulo {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 16px;
    }
    .fases-timeline {
        display: flex;
        align-items: flex-start;
        gap: 0;
        position: relative;
    }
    .fase-item {
        flex: 1;
        text-align: center;
        position: relative;
    }
    .fase-item::after {
        content: '';
        position: absolute;
        top: 24px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #dee2e6;
        z-index: 0;
    }
    .fase-item:last-child::after {
        display: none;
    }
    .fase-item.completo::after {
        background: #198754;
    }
    .fase-item.en_proceso::after {
        background: linear-gradient(90deg, #ffc107 0%, #dee2e6 100%);
    }
    .fase-circulo {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 1.2rem;
        position: relative;
        z-index: 1;
        transition: all 0.3s;
    }
    .fase-circulo.pendiente {
        background: #e9ecef;
        color: #6c757d;
        border: 2px solid #6c757d;
    }
    .fase-circulo.en_proceso {
        background: #fff3cd;
        color: #856404;
        border: 2px solid #ffc107;
        animation: pulse 2s infinite;
    }
    .fase-circulo.completo {
        background: #d1e7dd;
        color: #0f5132;
        border: 2px solid #198754;
    }
    .fase-circulo.bloqueado {
        background: #e9ecef;
        color: #6c757d;
        border: 2px dashed #adb5bd;
        opacity: 0.6;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.5); }
        50% { box-shadow: 0 0 0 8px rgba(255, 193, 7, 0); }
    }
    .fase-nombre {
        font-weight: 500;
        font-size: 0.85rem;
        color: #333;
        margin-bottom: 4px;
    }
    .fase-mensaje {
        font-size: 0.75rem;
        color: #6c757d;
        max-width: 140px;
        margin: 0 auto;
    }
    .fase-cantidad {
        font-size: 0.7rem;
        color: #0d6efd;
        font-weight: 500;
    }
    .fase-acciones {
        margin-top: 8px;
    }
    .fase-acciones .btn {
        font-size: 0.7rem;
        padding: 4px 10px;
    }
    .fase-bloqueado-overlay {
        position: relative;
    }
    .fase-bloqueado-overlay::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.5);
        z-index: 2;
        border-radius: 8px;
    }

    /* Alerta de fases incompletas */
    .fases-alerta {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .fases-alerta i {
        font-size: 1.5rem;
        color: #856404;
    }
    .fases-alerta-texto {
        flex: 1;
    }
    .fases-alerta-titulo {
        font-weight: 600;
        color: #856404;
    }
    .fases-alerta-desc {
        font-size: 0.85rem;
        color: #664d03;
    }
</style>
