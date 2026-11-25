<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   SISTEMA DE DISEÑO RESUCITADOS RCC — PANEL ADMINISTRATIVO (MOBILE-FIRST)
   Primary: #1B4D4F (Teal Profundo) | Secondary: #D97706 (Ámbar)
   Tertiary: #10B981 (Esmeralda)    | Dark Mode: Carbón/Grafito Neutro (Cero Azul)
   Headlines: Montserrat            | Body & Labels: Inter
   ========================================================================== */

:root {
    --rcc-primary: #1B4D4F;
    --rcc-primary-light: #2A8286;
    --rcc-secondary: #D97706;
    --rcc-secondary-light: #F59E0B;
    --rcc-tertiary: #10B981;
}

/* 1. TIPOGRAFÍA DEL PANEL (Montserrat en Títulos + Inter en Cuerpo/Labels) */
body,
.fi-body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

h1, h2, h3,
.fi-header-heading,
.fi-section-header-heading,
.fi-modal-heading,
.fi-ta-header-heading,
.fi-wi-stats-overview-stat-value {
    font-family: 'Montserrat', sans-serif !important;
    letter-spacing: -0.02em !important;
}

.fi-header-heading {
    font-weight: 800 !important;
}

/* ==========================================================================
   2. TEMA CLARO Y NUEVO TEMA OSCURO CARBÓN / GRAFITO ESMERALDA (SIN AZUL)
   ========================================================================== */
.fi-body {
    background-color: #F4F7F7 !important;
}

.dark .fi-body {
    /* Negro Carbón / Grafito neutro con sutil matiz petróleo (cero azul marino) */
    background-color: #0E1111 !important;
}

.fi-topbar nav {
    background-color: rgba(255, 255, 255, 0.92) !important;
    backdrop-filter: blur(14px) !important;
    -webkit-backdrop-filter: blur(14px) !important;
    border-bottom: 1px solid rgba(27, 77, 79, 0.14) !important;
}

.dark .fi-topbar nav {
    background-color: rgba(18, 22, 22, 0.92) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.fi-sidebar {
    background-color: #FFFFFF !important;
    border-right: 1px solid rgba(27, 77, 79, 0.12) !important;
}

.dark .fi-sidebar {
    background-color: #131717 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.07) !important;
}

/* Item activo en Sidebar con acento Teal + indicador Ámbar */
.fi-sidebar-item-active > a,
.fi-sidebar-item.fi-active > a {
    background: linear-gradient(135deg, #1B4D4F 0%, #236467 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(27, 77, 79, 0.3) !important;
    border-left: 3px solid #D97706 !important;
    border-radius: 0.75rem !important;
}

.fi-sidebar-item-active > a span,
.fi-sidebar-item-active > a svg,
.fi-sidebar-item.fi-active > a span,
.fi-sidebar-item.fi-active > a svg {
    color: #ffffff !important;
}

/* Secciones, tarjetas, tablas y modales en Modo Claro y Modo Oscuro Carbón */
.fi-section,
.fi-ta-ctn,
.fi-wi-stats-overview-stat {
    border-radius: 1rem !important;
    background-color: #FFFFFF !important;
    border: 1px solid rgba(27, 77, 79, 0.12) !important;
    box-shadow: 0 4px 16px -4px rgba(27, 77, 79, 0.06) !important;
}

.dark .fi-section,
.dark .fi-ta-ctn,
.dark .fi-wi-stats-overview-stat {
    background-color: #171C1C !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
}

.dark .fi-ta-header-toolbar,
.dark .fi-ta-table > thead > tr {
    background-color: #1C2222 !important;
}

/* Menú desplegable de Usuario y Selector de Tema (Claro / Oscuro / Sistema) */
.fi-dropdown-panel {
    border-radius: 1rem !important;
    background-color: #FFFFFF !important;
    border: 1px solid rgba(27, 77, 79, 0.16) !important;
    box-shadow: 0 14px 32px -6px rgba(15, 23, 42, 0.18) !important;
}

.dark .fi-dropdown-panel {
    background-color: #1A2020 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    box-shadow: 0 18px 38px -6px rgba(0, 0, 0, 0.75) !important;
}

/* Botones del selector de modo Claro / Oscuro / Sistema dentro del menú */
.fi-theme-switcher {
    padding: 0.35rem !important;
    gap: 0.35rem !important;
}

.fi-theme-switcher-btn {
    border-radius: 0.65rem !important;
    transition: all 0.18s ease !important;
}

.fi-theme-switcher-btn.fi-active {
    background-color: #1B4D4F !important;
    color: #FFFFFF !important;
    box-shadow: 0 2px 8px rgba(27, 77, 79, 0.35) !important;
}

.dark .fi-theme-switcher-btn.fi-active {
    background-color: #D97706 !important;
    color: #FFFFFF !important;
    box-shadow: 0 2px 10px rgba(217, 119, 6, 0.4) !important;
}

.fi-theme-switcher-btn.fi-active svg {
    color: #FFFFFF !important;
}

/* Botón Primario con gradiente Teal #1B4D4F */
.fi-btn-color-primary {
    background: linear-gradient(135deg, #1B4D4F 0%, #246669 100%) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    box-shadow: 0 4px 12px rgba(27, 77, 79, 0.3) !important;
    border-radius: 0.65rem !important;
    font-weight: 600 !important;
}

.fi-btn-color-primary:hover {
    background: linear-gradient(135deg, #215E61 0%, #2C7A7E 100%) !important;
    transform: translateY(-1px);
}

/* Espaciado limpio entre botones en tarjetas de Mi Perfil (ej. 2FA: Regenerar códigos + Deshabilitar) */
.fi-section-footer,
.fi-section-footer > div,
.fi-form-actions .fi-ac,
.fi-section .flex:has(> .fi-btn) {
    gap: 0.75rem !important;
    flex-wrap: wrap !important;
}

@media (max-width: 639px) {
    .fi-section-footer .fi-btn,
    .fi-section .flex:has(> .fi-btn) > .fi-btn {
        width: 100% !important;
        justify-content: center !important;
    }
}

/* ==========================================================================
   3. TABLAS EN ESCRITORIO (>= 640px) Y TARJETAS EN CELULAR (< 640px)
   ========================================================================== */
@media (min-width: 640px) {
    .fi-ta-table td.fi-ta-actions-cell,
    .fi-ta-table th.fi-ta-actions-header-cell,
    .fi-ta-table tr > td.fi-ta-cell:has(.fi-ta-actions),
    .fi-ta-table tr > th:last-child:has(.fi-ta-actions) {
        position: sticky !important;
        right: 0 !important;
        z-index: 2 !important;
        background-color: #ffffff !important;
        box-shadow: -6px 0 14px -4px rgba(15, 23, 42, 0.08) !important;
        border-left: 1px solid rgba(27, 77, 79, 0.1) !important;
    }

    .dark .fi-ta-table td.fi-ta-actions-cell,
    .dark .fi-ta-table th.fi-ta-actions-header-cell,
    .dark .fi-ta-table tr > td.fi-ta-cell:has(.fi-ta-actions),
    .dark .fi-ta-table tr > th:last-child:has(.fi-ta-actions) {
        background-color: #171C1C !important;
        box-shadow: -8px 0 16px -4px rgba(0, 0, 0, 0.55) !important;
        border-left: 1px solid rgba(255, 255, 255, 0.07) !important;
    }
}

/* Elevar la fila y celda cuyo menú de 3 puntos (⋮) está abierto por encima de todas las demás filas */
.fi-ta-table tr.fi-ta-row:has(.fi-dropdown-panel:not([style*="display: none"])),
.fi-ta-table tr.fi-ta-row:focus-within {
    z-index: 60 !important;
}

.fi-ta-table tr > td.fi-ta-cell:has(.fi-dropdown-panel:not([style*="display: none"])),
.fi-ta-table tr > td.fi-ta-cell:has(.fi-ta-actions):focus-within {
    z-index: 65 !important;
}

.fi-ta-actions .fi-dropdown-panel {
    z-index: 999 !important;
}

/* Botón de 3 puntos (⋮) grande, táctil y evidente a primera vista */
.fi-ta-actions .fi-icon-btn,
.fi-ta-actions .fi-ac-icon-btn-group,
.fi-ta-actions button[aria-haspopup="true"],
.fi-ta-actions .fi-dropdown-trigger > button {
    width: 2.35rem !important;
    height: 2.35rem !important;
    min-width: 2.35rem !important;
    min-height: 2.35rem !important;
    border-radius: 0.65rem !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(27, 77, 79, 0.1) !important;
    border: 1px solid rgba(27, 77, 79, 0.28) !important;
    color: #1B4D4F !important;
    transition: all 0.18s ease !important;
}

.dark .fi-ta-actions .fi-icon-btn,
.dark .fi-ta-actions .fi-ac-icon-btn-group,
.dark .fi-ta-actions button[aria-haspopup="true"],
.dark .fi-ta-actions .fi-dropdown-trigger > button {
    background: rgba(217, 119, 6, 0.16) !important;
    border: 1px solid rgba(217, 119, 6, 0.42) !important;
    color: #FBBF24 !important;
}

.fi-ta-actions .fi-icon-btn:hover,
.fi-ta-actions .fi-dropdown-trigger > button:hover {
    background: #1B4D4F !important;
    border-color: #1B4D4F !important;
    color: #ffffff !important;
}

.dark .fi-ta-actions .fi-icon-btn:hover,
.dark .fi-ta-actions .fi-dropdown-trigger > button:hover {
    background: #D97706 !important;
    border-color: #F59E0B !important;
    color: #ffffff !important;
}

/* ==========================================================================
   4. TARJETAS EN CELULAR (< 640px): LECTURA CLARA Y SIN SOLAPAR EL BOTÓN ⋮
   ========================================================================== */
@media (max-width: 639px) {
    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
        padding: 0.75rem !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row:not(.fi-ta-group-header-row) {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0 !important;
        padding: 1rem !important;
        border-radius: 1rem !important;
        background-color: #ffffff !important;
        border: 1px solid rgba(27, 77, 79, 0.16) !important;
        box-shadow: 0 4px 14px -3px rgba(27, 77, 79, 0.08) !important;
    }

    .dark .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row:not(.fi-ta-group-header-row) {
        background-color: #1D2323 !important;
        border: 1px solid rgba(255, 255, 255, 0.09) !important;
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5) !important;
    }

    /* Cada campo dentro de la tarjeta es una fila horizontal clara */
    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row:not(.fi-ta-group-header-row) > td.fi-ta-cell:not(.fi-ta-selection-cell):not(:has(.fi-ta-actions)) {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.75rem !important;
        padding: 0.55rem 0 !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.14) !important;
        width: 100% !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row:not(.fi-ta-group-header-row) > td.fi-ta-cell:not(.fi-ta-selection-cell):not(:has(.fi-ta-actions)):last-of-type {
        border-bottom: none !important;
        padding-bottom: 0 !important;
    }

    /* Etiqueta del campo a la izquierda */
    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell > .fi-ta-cell-label {
        padding-top: 0 !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #52606D !important;
        flex-shrink: 0 !important;
        max-width: 38% !important;
        line-height: 1.25 !important;
    }

    .dark .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell > .fi-ta-cell-label {
        color: #A1A1AA !important;
    }

    /* Valor del campo a la derecha */
    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell > .fi-ta-cell-content {
        flex: 1 !important;
        display: flex !important;
        justify-content: flex-end !important;
        text-align: right !important;
        white-space: normal !important;
        word-break: break-word !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell > .fi-ta-cell-content .fi-ta-col {
        justify-content: flex-end !important;
        text-align: right !important;
        width: auto !important;
        max-width: 100% !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell .fi-ta-text {
        align-items: flex-end !important;
        text-align: right !important;
        white-space: normal !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row .fi-badge {
        max-width: 100% !important;
        white-space: normal !important;
        text-align: center !important;
    }

    /* Barra superior dedicada dentro de cada tarjeta móvil para las acciones (⋮, Eliminar, Ver conteo, etc.) sin encimarse nunca con los datos */
    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell:has(.fi-ta-actions) {
        position: static !important;
        order: -1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        padding: 0 0 0.6rem 0 !important;
        margin-bottom: 0.15rem !important;
        border-bottom: 1px dashed rgba(148, 163, 184, 0.22) !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell:has(.fi-ta-actions)::before {
        content: 'ACCIONES';
        font-family: 'Montserrat', sans-serif;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #1B4D4F;
    }

    .dark .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell:has(.fi-ta-actions)::before {
        color: #D97706;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-cell:has(.fi-ta-actions) > .fi-ta-actions {
        width: auto !important;
        justify-content: flex-end !important;
        margin-left: auto !important;
    }

    .fi-ta-table.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row > td.fi-ta-selection-cell,
    .fi-ta-table-stacked-header-row .fi-ta-page-checkbox {
        display: none !important;
    }
}

/* ==========================================================================
   5. NAVEGACIÓN MÓVIL (< 1024px) ADAPTABLE A MODO CLARO Y MODO OSCURO
   ========================================================================== */
@media (max-width: 1023px) {
    .fi-topbar-open-sidebar-btn,
    .fi-topbar-close-sidebar-btn {
        display: none !important;
    }

    .fi-main {
        padding-bottom: 5.75rem !important;
    }
}

@media (max-width: 767px) {
    .fi-main {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .fi-header {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.65rem !important;
    }

    .fi-header-heading {
        font-size: 1.35rem !important;
        line-height: 1.75rem !important;
    }

    .fi-header-actions {
        width: 100% !important;
        display: flex !important;
    }

    .fi-header-actions > .fi-btn,
    .fi-header-actions > .fi-ac-btn-action {
        flex: 1 !important;
        justify-content: center !important;
        padding-top: 0.65rem !important;
        padding-bottom: 0.65rem !important;
        font-size: 0.925rem !important;
        border-radius: 0.75rem !important;
    }
}

/* Logo compacto en la barra superior móvil adaptable a Claro y Oscuro */
.rcc-mobile-topbar-brand {
    display: none;
}

@media (max-width: 1023px) {
    .rcc-mobile-topbar-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        margin-right: auto;
    }

    .rcc-mobile-topbar-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        font-size: 0.95rem;
        color: #1B4D4F;
    }

    .dark .rcc-mobile-topbar-title {
        color: #FFFFFF;
    }
}

/* Dock Inferior Flotante + Bandeja (Bottom Sheet) con Soporte Claro y Oscuro */
.rcc-mobile-dock,
.rcc-modules-sheet-backdrop,
.rcc-modules-sheet {
    display: none;
}

@media (max-width: 1023px) {
    /* MODO CLARO por defecto para el Dock Inferior */
    .rcc-mobile-dock {
        position: fixed;
        bottom: 0.75rem;
        left: 0.75rem;
        right: 0.75rem;
        max-width: 28rem;
        margin: 0 auto;
        z-index: 45;
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding: 0.4rem 0.5rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(27, 77, 79, 0.18);
        box-shadow: 0 12px 28px -4px rgba(27, 77, 79, 0.2);
    }

    /* MODO OSCURO CARBÓN para el Dock Inferior */
    .dark .rcc-mobile-dock {
        background: rgba(22, 27, 27, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 14px 32px -6px rgba(0, 0, 0, 0.75);
    }

    .rcc-mobile-dock-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        padding: 0.35rem 0.6rem;
        border-radius: 9999px;
        color: #475569;
        font-size: 0.64rem;
        font-weight: 600;
        text-decoration: none;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        max-width: 4.8rem;
    }

    .rcc-mobile-dock-item span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .dark .rcc-mobile-dock-item {
        color: #A1A1AA;
    }

    .rcc-mobile-dock-item svg {
        width: 1.22rem;
        height: 1.22rem;
    }

    .rcc-mobile-dock-item.is-active {
        background: #1B4D4F;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(27, 77, 79, 0.4);
    }

    .rcc-mobile-dock-item.is-active svg {
        color: #FBBF24;
    }

    /* Botón central destacado "Módulos" */
    .rcc-mobile-dock-trigger {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        padding: 0.4rem 0.95rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.4);
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .rcc-mobile-dock-trigger svg {
        width: 1.25rem;
        height: 1.25rem;
        color: #ffffff;
    }

    .rcc-mobile-dock-trigger:active {
        transform: scale(0.95);
    }

    /* Fondo oscuro de la bandeja de módulos */
    .rcc-modules-sheet-backdrop {
        position: fixed;
        inset: 0;
        z-index: 50;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
        display: block;
    }

    .rcc-modules-sheet-backdrop.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    /* Bandeja deslizable desde abajo adaptable a Modo Claro y Modo Oscuro */
    .rcc-modules-sheet {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 55;
        max-height: 82vh;
        display: flex;
        flex-direction: column;
        background: #FFFFFF;
        border-top: 1px solid rgba(27, 77, 79, 0.16);
        border-radius: 1.5rem 1.5rem 0 0;
        box-shadow: 0 -16px 40px rgba(15, 23, 42, 0.2);
        transform: translateY(105%);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dark .rcc-modules-sheet {
        background: #141818;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 -16px 40px rgba(0, 0, 0, 0.75);
    }

    .rcc-modules-sheet.is-open {
        transform: translateY(0);
    }

    .rcc-modules-sheet-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem 0.75rem;
        border-bottom: 1px solid rgba(27, 77, 79, 0.1);
    }

    .dark .rcc-modules-sheet-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .rcc-modules-sheet-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        font-size: 0.95rem;
        color: #1B4D4F;
    }

    .dark .rcc-modules-sheet-title {
        color: #FFFFFF;
    }

    .rcc-modules-sheet-close {
        background: rgba(27, 77, 79, 0.08);
        border: none;
        color: #1B4D4F;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: 700;
    }

    .dark .rcc-modules-sheet-close {
        background: rgba(255, 255, 255, 0.08);
        color: #A1A1AA;
    }

    .rcc-modules-sheet-body {
        padding: 1rem 1.15rem 2rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .rcc-modules-section-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #D97706;
        margin: 0.75rem 0 0.6rem;
    }

    .rcc-modules-section-title:first-child {
        margin-top: 0;
    }

    .rcc-modules-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .rcc-module-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 0.4rem;
        padding: 0.75rem 0.45rem;
        border-radius: 0.9rem;
        background: #F4F7F7;
        border: 1px solid rgba(27, 77, 79, 0.12);
        color: #1E293B;
        text-decoration: none;
        font-size: 0.72rem;
        font-weight: 600;
        line-height: 1.2;
        transition: all 0.18s ease;
    }

    .dark .rcc-module-card {
        background: #1D2323;
        border: 1px solid rgba(255, 255, 255, 0.07);
        color: #E4E4E7;
    }

    .rcc-module-card-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(27, 77, 79, 0.12);
        color: #1B4D4F;
    }

    .dark .rcc-module-card-icon {
        background: rgba(27, 77, 79, 0.35);
        color: #5FC2C6;
    }

    .rcc-module-card-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .rcc-module-card.is-active {
        background: linear-gradient(135deg, #1B4D4F 0%, #236467 100%) !important;
        border-color: #D97706 !important;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(27, 77, 79, 0.35) !important;
    }

    .rcc-module-card.is-active .rcc-module-card-icon {
        background: rgba(217, 119, 6, 0.25) !important;
        color: #FBBF24 !important;
    }
}

/* ==========================================================================
   6. OPTIMIZACIÓN RESPONSIVA PARA WIZARD DE EVENTOS (MÓVIL Y ESCRITORIO)
   ========================================================================== */
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

.rcc-event-wizard .fi-sc-wizard-header-step,
.rcc-event-wizard .fi-sc-wizard-header-step:not(.fi-active) {
    display: flex !important;
    flex-shrink: 0 !important;
    align-items: center !important;
}

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

.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-btn {
    background: rgba(27, 77, 79, 0.25) !important;
    border-color: #D97706 !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-number {
    color: #D97706 !important;
    font-weight: 700 !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-label {
    color: #FBBF24 !important;
    font-weight: 600 !important;
}

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
    border-color: #D97706 !important;
    background: rgba(217, 119, 6, 0.2) !important;
}

.rcc-event-wizard .fi-sc-wizard-header-step.fi-completed .fi-sc-wizard-header-step-icon-ctn {
    border-color: #10B981 !important;
    background: rgba(16, 185, 129, 0.2) !important;
    color: #10B981 !important;
}

@media (max-width: 767px) {
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

    .rcc-event-wizard .fi-sc-wizard-header-step-separator,
    .rcc-event-wizard .fi-sc-wizard-header-step-description {
        display: none !important;
    }

    .rcc-event-wizard .fi-sc-wizard-header-step-label {
        font-size: 0.75rem !important;
        white-space: nowrap !important;
    }

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

@media (min-width: 768px) {
    .rcc-event-wizard .fi-sc-wizard-footer {
        display: none !important;
    }
}

.fi-simple-layout {
    transition: background-image 0.8s ease-in-out;
}
</style>

@if(auth()->check() && ! request()->routeIs('filament.admin.auth.*'))
    @php
        $u = auth()->user();
        $currPath = request()->path();

        $attendanceModule = [
            'key' => 'attendance',
            'label' => 'Asistencia',
            'url' => url('/admin/attendances'),
            'match' => 'attendances',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        ];

        $adminModules = [
            ['key' => 'assistants', 'label' => 'Asistentes', 'url' => url('/admin/administration/assistants'), 'match' => 'administration/assistants', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />'],
            ['key' => 'banners', 'label' => 'Banners', 'url' => url('/admin/administration/banners'), 'match' => 'administration/banners', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />'],
            ['key' => 'songs', 'label' => 'Canciones', 'url' => url('/admin/administration/songs'), 'match' => 'administration/songs', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />'],
            ['key' => 'contacts', 'label' => 'Contactos', 'url' => url('/admin/administration/contacts'), 'match' => 'administration/contacts', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />'],
            ['key' => 'polls', 'label' => 'Encuestas', 'url' => url('/admin/administration/polls'), 'match' => 'administration/polls', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />'],
            ['key' => 'team_members', 'label' => 'Equipo Servidores', 'url' => url('/admin/administration/team-members'), 'match' => 'administration/team-members', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />'],
            ['key' => 'events', 'label' => 'Eventos', 'url' => url('/admin/administration/events'), 'match' => 'administration/events', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />'],
            ['key' => 'reflections', 'label' => 'Lecturas y Refl.', 'url' => url('/admin/administration/reflections'), 'match' => 'administration/reflections', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />'],
            ['key' => 'ministries', 'label' => 'Ministerios', 'url' => url('/admin/administration/ministries'), 'match' => 'administration/ministries', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />'],
            ['key' => 'prayers', 'label' => 'Oraciones', 'url' => url('/admin/administration/prayers'), 'match' => 'administration/prayers', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />'],
            ['key' => 'petitions', 'label' => 'Peticiones', 'url' => url('/admin/administration/petitions'), 'match' => 'administration/petitions', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />'],
            ['key' => 'social_media', 'label' => 'Redes y Medios', 'url' => url('/admin/administration/social-media-items'), 'match' => 'administration/social-media-items', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />'],
            ['key' => 'reports', 'label' => 'Reportes', 'url' => url('/admin/reports'), 'match' => 'admin/reports', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />'],
        ];

        $configModules = [
            ['key' => 'event_types', 'label' => 'Tipo de Eventos', 'url' => url('/admin/configuration/event-types'), 'match' => 'configuration/event-types', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />'],
            ['key' => 'users', 'label' => 'Usuarios', 'url' => url('/admin/configuration/users'), 'match' => 'configuration/users', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />'],
        ];

        $visibleAdminModules = array_values(array_filter($adminModules, fn ($m) => $u->hasModuleAccess($m['key'])));
        $visibleConfigModules = array_values(array_filter($configModules, fn ($m) => $u->hasModuleAccess($m['key'])));

        /* Construir dinámicamente los accesos rápidos del Dock Inferior SOLO con módulos a los que el usuario tiene permiso (sin quemar módulos ni repetir Perfil) */
        $allPermittedPool = [];
        if ($u->hasModuleAccess('attendance')) {
            $allPermittedPool[] = $attendanceModule;
        }
        /* Priorizar en el dock rápido los módulos operativos si el usuario tiene permiso, seguidos del resto de sus módulos permitidos */
        $priorityOrder = ['events', 'assistants', 'ministries', 'contacts', 'petitions', 'polls', 'reflections', 'prayers', 'songs', 'banners', 'team_members', 'social_media', 'reports'];
        foreach ($priorityOrder as $pKey) {
            foreach ($visibleAdminModules as $m) {
                if ($m['key'] === $pKey) {
                    $allPermittedPool[] = $m;
                    break;
                }
            }
        }
        foreach ($visibleConfigModules as $m) {
            $allPermittedPool[] = $m;
        }

        /* Tomar hasta 3 módulos permitidos para acompañar a "Inicio" y al botón central "Módulos" */
        $quickModules = array_slice($allPermittedPool, 0, 3);
        $leftQuickModule = $quickModules[0] ?? null;
        $rightQuickModules = array_slice($quickModules, 1, 2);
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* 1. Colocar el logo RESUCITADOS · PANEL ADMINISTRATIVO en la barra superior móvil */
            const topbarNav = document.querySelector('.fi-topbar nav');
            if (topbarNav && !document.getElementById('rcc-mobile-topbar-brand')) {
                const brand = document.createElement('a');
                brand.id = 'rcc-mobile-topbar-brand';
                brand.href = '{{ url('/admin') }}';
                brand.className = 'rcc-mobile-topbar-brand';
                brand.innerHTML = `
                    <img src="{{ asset('icon.png') }}" alt="Logo" style="height: 2rem; width: auto;" />
                    <div style="display: flex; flex-direction: column; line-height: 1.05;">
                        <span class="rcc-mobile-topbar-title">RESUCITADOS</span>
                        <span style="font-family: 'Inter', sans-serif; font-weight: 700; font-size: 0.58rem; letter-spacing: 0.1em; color: #D97706;">PANEL ADMINISTRATIVO</span>
                    </div>
                `;
                topbarNav.insertBefore(brand, topbarNav.firstChild);
            }

            /* 2. Crear el Dock Flotante Inferior Dinámico + Bandeja de Todos los Módulos */
            if (document.getElementById('rcc-mobile-dock')) return;

            const backdrop = document.createElement('div');
            backdrop.id = 'rcc-modules-sheet-backdrop';
            backdrop.className = 'rcc-modules-sheet-backdrop';

            const sheet = document.createElement('div');
            sheet.id = 'rcc-modules-sheet';
            sheet.className = 'rcc-modules-sheet';
            sheet.innerHTML = `
                <div class="rcc-modules-sheet-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 0.5rem; height: 0.5rem; border-radius: 9999px; background: #D97706;"></span>
                        <span class="rcc-modules-sheet-title">Módulos Habilitados</span>
                    </div>
                    <button type="button" id="rcc-close-modules-sheet" class="rcc-modules-sheet-close">
                        ✕
                    </button>
                </div>
                <div class="rcc-modules-sheet-body">
                    <div class="rcc-modules-section-title">Principal</div>
                    <div class="rcc-modules-grid">
                        <a href="{{ url('/admin') }}" class="rcc-module-card {{ $currPath === 'admin' ? 'is-active' : '' }}">
                            <div class="rcc-module-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            </div>
                            <span>Escritorio</span>
                        </a>
                        @if($u->hasModuleAccess('attendance'))
                        <a href="{{ url('/admin/attendances') }}" class="rcc-module-card {{ str_contains($currPath, 'attendances') ? 'is-active' : '' }}">
                            <div class="rcc-module-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            </div>
                            <span>Asistencia</span>
                        </a>
                        @endif
                    </div>

                    @if(count($visibleAdminModules) > 0)
                    <div class="rcc-modules-section-title">Administración (A–Z)</div>
                    <div class="rcc-modules-grid">
                        @foreach($visibleAdminModules as $mod)
                        <a href="{{ $mod['url'] }}" class="rcc-module-card {{ str_contains($currPath, $mod['match']) ? 'is-active' : '' }}">
                            <div class="rcc-module-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $mod['icon'] !!}</svg>
                            </div>
                            <span>{{ $mod['label'] }}</span>
                        </a>
                        @endforeach
                    </div>
                    @endif

                    @if(count($visibleConfigModules) > 0)
                    <div class="rcc-modules-section-title">Configuración</div>
                    <div class="rcc-modules-grid">
                        @foreach($visibleConfigModules as $mod)
                        <a href="{{ $mod['url'] }}" class="rcc-module-card {{ str_contains($currPath, $mod['match']) ? 'is-active' : '' }}">
                            <div class="rcc-module-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $mod['icon'] !!}</svg>
                            </div>
                            <span>{{ $mod['label'] }}</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            `;

            const dock = document.createElement('nav');
            dock.id = 'rcc-mobile-dock';
            dock.className = 'rcc-mobile-dock';
            dock.setAttribute('aria-label', 'Navegación móvil');
            dock.innerHTML = `
                <a href="{{ url('/admin') }}" class="rcc-mobile-dock-item {{ $currPath === 'admin' ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    <span>Inicio</span>
                </a>
                @if($leftQuickModule)
                <a href="{{ $leftQuickModule['url'] }}" class="rcc-mobile-dock-item {{ str_contains($currPath, $leftQuickModule['match']) ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">{!! $leftQuickModule['icon'] !!}</svg>
                    <span>{{ $leftQuickModule['label'] }}</span>
                </a>
                @endif
                <button type="button" id="rcc-open-modules-sheet" class="rcc-mobile-dock-trigger">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    <span>Módulos</span>
                </button>
                @foreach($rightQuickModules as $rm)
                <a href="{{ $rm['url'] }}" class="rcc-mobile-dock-item {{ str_contains($currPath, $rm['match']) ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">{!! $rm['icon'] !!}</svg>
                    <span>{{ $rm['label'] }}</span>
                </a>
                @endforeach
            `;

            document.body.appendChild(backdrop);
            document.body.appendChild(sheet);
            document.body.appendChild(dock);

            const openBtn = document.getElementById('rcc-open-modules-sheet');
            const closeBtn = document.getElementById('rcc-close-modules-sheet');

            function toggleSheet(open) {
                if (open) {
                    backdrop.classList.add('is-open');
                    sheet.classList.add('is-open');
                } else {
                    backdrop.classList.remove('is-open');
                    sheet.classList.remove('is-open');
                }
            }

            if (openBtn) {
                openBtn.addEventListener('click', function () {
                    toggleSheet(!sheet.classList.contains('is-open'));
                });
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', function () { toggleSheet(false); });
            }
            backdrop.addEventListener('click', function () { toggleSheet(false); });
        });
    </script>
@endif

@if(request()->routeIs('filament.admin.auth.*'))
    @php
        $loginBannerUrls = [];
        try {
            $loginBannerUrls = \App\Models\Banner::query()
                ->active()
                ->forSection(\App\Models\Banner::SECTION_ADMIN_LOGIN)
                ->orderBy('sort_order')
                ->get()
                ->flatMap(fn (\App\Models\Banner $b): array => $b->getImagePaths())
                ->filter(fn (string $p): bool => ! \App\Models\Banner::isVideoPath($p))
                ->map(fn (string $p): string => str_starts_with($p, 'http') ? $p : '/storage/' . ltrim($p, '/'))
                ->values()
                ->all();
        } catch (\Throwable) {
            $loginBannerUrls = [];
        }
    @endphp
    @if(count($loginBannerUrls) > 0)
        <style>
            :root {
                --filament-backgrounds-image: url("{{ $loginBannerUrls[0] }}") !important;
            }
        </style>
        @if(count($loginBannerUrls) > 1)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const urls = @json($loginBannerUrls);
                    let idx = 0;
                    urls.forEach(function (u) { const img = new Image(); img.src = u; });
                    setInterval(function () {
                        idx = (idx + 1) % urls.length;
                        document.documentElement.style.setProperty('--filament-backgrounds-image', 'url("' + urls[idx] + '")');
                    }, 6000);
                });
            </script>
        @endif
    @endif
@endif
