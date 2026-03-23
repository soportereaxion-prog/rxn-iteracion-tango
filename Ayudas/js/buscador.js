/**
 * Motor de búsqueda interno para sistema de Ayudas RXN
 * - Sin dependencias
 * - Indexación en memoria
 * - Fetching dinámico de archivos HTML predefinidos
 */

const ayudas = [
    "ProcesarDatoss.html",
    "Reprocesar rechazados.html",
    "LimpiarArchivos.html",
    "RechazarPendientes.html",
    "CopiarFacturas.html",
    "ConfiguracionDeDirectorio.html"
];

let indiceAyudas = [];

async function inicializarBuscador() {
    for (const archivo of ayudas) {
        try {
            const resp = await fetch(archivo);
            if (resp.ok) {
                const html = await resp.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                
                // Extraer el título del card header si existe, o usar el nombre del archivo
                const headerNode = doc.querySelector('.rxn-card-header');
                const titulo = headerNode ? headerNode.innerText.trim() : archivo.replace('.html', '');
                
                // Limpiar el contenido HTML a texto puro, reemplazando saltos de línea por espacios
                const contenido = doc.body.innerText.replace(/\s+/g, ' ').trim();
                
                indiceAyudas.push({ archivo, titulo, contenido });
            }
        } catch (e) {
            console.error("RXN Buscador: Error indexando el archivo " + archivo, e);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    // 1. Iniciar indexación en background
    inicializarBuscador();
    
    // 2. Asociar eventos al input
    const inputSearch = document.getElementById("inputBuscadorAyuda");
    const contenedorResultados = document.getElementById("resultadosBuscador");
    
    if (inputSearch && contenedorResultados) {
        inputSearch.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase().trim();
            
            // Requerir al menos 3 caracteres
            if (query.length < 3) {
                contenedorResultados.innerHTML = "";
                return;
            }
            
            // Buscar coincidencias en título o contenido
            const resultados = indiceAyudas.filter(item => 
                item.titulo.toLowerCase().includes(query) || 
                item.contenido.toLowerCase().includes(query)
            );
            
            renderizarResultados(resultados, query, contenedorResultados);
        });
    }
});

function renderizarResultados(resultados, query, contenedor) {
    if (resultados.length === 0) {
        contenedor.innerHTML = '<div style="padding: 10px; color: var(--rxn-text-muted); font-size: 12px; text-align: center;">No hay coincidencias.</div>';
        return;
    }
    
    let htmlResultados = '';
    
    resultados.forEach(res => {
        // Encontrar la posición de la ocurrencia para extraer un snippet
        const idx = res.contenido.toLowerCase().indexOf(query);
        let snippet = "";
        
        if (idx !== -1) {
            const start = Math.max(0, idx - 30);
            const end = Math.min(res.contenido.length, idx + query.length + 30);
            snippet = res.contenido.substring(start, end);
            
            // Resaltar la coincidencia
            const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
            snippet = snippet.replace(regex, '<strong style="color: var(--rxn-input-focus); background-color: rgba(61, 142, 201, 0.1);">$1</strong>');
            snippet = "..." + snippet + "...";
        } else {
            // Si la coincidencia fue en el título, mostrar el inicio del contenido
            snippet = res.contenido.substring(0, 60) + "...";
        }
        
        // Renderizar el ítem de resultado
        htmlResultados += `
            <div style="padding: 10px; border-bottom: 1px solid var(--rxn-table-odd); cursor: pointer; transition: background 0.2s;" 
                 onmouseover="this.style.backgroundColor='var(--rxn-table-hover)'" 
                 onmouseout="this.style.backgroundColor='transparent'"
                 onclick="window.location.href='MenuPrincipal.html?ayuda=${res.archivo}'">
                <div style="font-weight: 600; font-size: 13px; color: var(--rxn-btn-primary-bg); line-height: 1.2; margin-bottom: 4px;">${res.titulo}</div>
                <div style="font-size: 11px; color: var(--rxn-text-muted); line-height: 1.3;">${snippet}</div>
            </div>
        `;
    });
    
    contenedor.innerHTML = htmlResultados;
}

// Función auxiliar para escapar caracteres en RegEx
function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
