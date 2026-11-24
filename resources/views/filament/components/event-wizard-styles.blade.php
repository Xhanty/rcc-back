<style>
/* ==========================================================================
   OPTIMIZACIÓN RESPONSIVA PARA WIZARD DE EVENTOS (MÓVIL Y ESCRITORIO)
   ========================================================================== */

/* 1. Header general: Mostrar siempre todos los pasos */
.rcc-event-wizard .fi-sc-wizard-header {
    display: flex !important;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch !important;
    scroll-behavior: smooth !important;
    gap: 0.5rem !important;
    padding: 0.5rem !important;
    scrollbar-width: thin !important;
    border-radius: 0.75rem 0.75rem 0 0 !important;
}

/* Forzar que TODOS los pasos se muestren siempre (incluso en pantallas móviles donde Filament los oculta) */
.rcc-event-wizard .fi-sc-wizard-header-step,
.rcc-event-wizard .fi-sc-wizard-header-step:not(.fi-active) {
    display: flex !important;
    flex-shrink: 0 !important;
    align-items: center !important;
}

/* Botón interactivo de cada paso */
.rcc-event-wizard .fi-sc-wizard-header-step-btn {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.5rem 0.75rem !important;
    border-radius: 0.5rem !important;
    border: 1px solid transparent !important;
    transition: all 0.2s ease-in-out !important;
    cursor: pointer !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step-btn:hover {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Paso ACTIVO: Resaltado visible en ámbar */
.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-btn {
    background: rgba(217, 119, 6, 0.15) !important;
    border-color: rgba(217, 119, 6, 0.45) !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-number {
    color: #f59e0b !important;
    font-weight: 700 !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-label {
    color: #fbbf24 !important;
    font-weight: 600 !important;
}

/* Círculo con número o icono */
.rcc-event-wizard .fi-sc-wizard-header-step-icon-ctn {
    width: 2rem !important;
    height: 2rem !important;
    min-width: 2rem !important;
    border-radius: 9999px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.8125rem !important;
    border: 2px solid rgba(255, 255, 255, 0.2) !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-icon-ctn {
    border-color: #f59e0b !important;
    background: rgba(245, 158, 11, 0.2) !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-completed .fi-sc-wizard-header-step-icon-ctn {
    border-color: #10b981 !important;
    background: rgba(16, 185, 129, 0.2) !important;
    color: #10b981 !important;
}

/* 2. REGLAS ESPECÍFICAS PARA CELULARES (Móvil < 768px) */
@media (max-width: 767px) {
    /* Barra superior deslizable con scroll táctil sin scrollbar molesta */
    .rcc-event-wizard .fi-sc-wizard-header {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        padding: 0.375rem !important;
        gap: 0.375rem !important;
        background: rgba(0, 0, 0, 0.2) !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header::-webkit-scrollbar {
        display: none !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-btn {
        padding: 0.375rem 0.625rem !important;
        gap: 0.375rem !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-icon-ctn {
        width: 1.625rem !important;
        height: 1.625rem !important;
        min-width: 1.625rem !important;
        font-size: 0.6875rem !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-separator {
        display: none !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-text {
        max-width: none !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-label {
        font-size: 0.75rem !important;
        white-space: nowrap !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-description {
        display: none !important;
    }

    /* En móvil: mostrar botones Anterior / Siguiente en la parte inferior para máxima comodidad */
    .rcc-event-wizard .fi-sc-wizard-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.75rem !important;
        padding-top: 1.25rem !important;
        margin-top: 1.25rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .rcc-event-wizard .fi-sc-wizard-footer > div {
        flex: 1 !important;
    }

    .rcc-event-wizard .fi-sc-wizard-footer .fi-btn {
        width: 100% !important;
        justify-content: center !important;
        padding-block: 0.625rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
    }
}

/* 3. REGLAS PARA ESCRITORIO (>= 768px) */
@media (min-width: 768px) {
    /* En escritorio se oculta el footer para que la navegación sea 100% por el selector superior */
    .rcc-event-wizard .fi-sc-wizard-footer {
        display: none !important;
    }
}
</style>
