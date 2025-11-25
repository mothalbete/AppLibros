function mostrarPreview() {
    const select = document.getElementById("imagenSelect");
    const preview = document.getElementById("preview");
    const archivo = select.value;

    if (archivo) {
        preview.src = "upload/" + archivo;
        preview.style.display = "block";
    } else {
        preview.style.display = "none";
    }
}

// Mostrar la primera imagen por defecto al cargar la página
window.onload = mostrarPreview;
