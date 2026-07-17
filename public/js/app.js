class appPAW {
	constructor() {
		document.addEventListener("DOMContentLoaded", () => {
			// Toggle de menú hamburguesa móvil
			const hamburger = document.querySelector(".hamburger-icon");
			const navBar = document.querySelector(".nav-bar");
			if (hamburger && navBar) {
				hamburger.addEventListener("click", () => {
					navBar.classList.toggle("nav-bar-active");
					const icon = hamburger.querySelector("i");
					if (icon) {
						icon.classList.toggle("fa-bars");
						icon.classList.toggle("fa-xmark");
					}
				});
			}

			PAW.cargarScript("DragDropArchivo", "js/components/drag-drop-archivo.js", () => {
				new DragAndDropArchivo("#dropzone", "#recursoArchivo", "#preview");
			});
			PAW.cargarScript("EnlaceRecurso", "js/components/enlaceRecurso.js", () => {
				 new EnlaceRecurso(
					"#usarEnlace","#zonaEnlace", "#dropzone", "#botonSeleccionarArchivo", "#recursoArchivo", 
					"#recursoLink", "#preview"
				);
			})
		});
	}
}

let app = new appPAW();


