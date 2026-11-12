'use strict';

document.addEventListener('DOMContentLoaded', () => {
    menuMovil();
    alertasCerrables();
    confirmaciones();
    validacionFormularios();
    contadoresCaracteres();
    verClave();
    camposPorCondicion();
    chips();
    filasIngredientes();
    calculadoraNutricional();
    nutricionPorPorcion();
    rangosSincronizados();
    filtroInstantaneo();
    panelFiltrosMovil();
    autoEnviar();
    modales();
    imprimir();
});

/** Selecciona todos los elementos que coinciden. */
const $$ = (selector, raiz = document) => Array.from(raiz.querySelectorAll(selector));

/** Formatea números con coma decimal (es-CR). */
const formato = (n) => (Math.round(n * 10) / 10).toLocaleString('es-CR', { maximumFractionDigits: 1 });

/* 1) Menú del navbar en pantallas pequeñas -------------------------------- */
function menuMovil() {
    const boton = document.querySelector('[data-nav-toggle]');
    const menu = boton && document.getElementById(boton.getAttribute('aria-controls'));
    if (!boton || !menu) return;
    boton.addEventListener('click', () => {
        const abierto = boton.getAttribute('aria-expanded') === 'true';
        boton.setAttribute('aria-expanded', String(!abierto));
        boton.setAttribute('aria-label', abierto ? 'Abrir menú' : 'Cerrar menú');
        menu.classList.toggle('navbar__menu--abierto', !abierto);
    });
}

/* 2) Mensajes que se pueden cerrar --------------------------------------- */
function alertasCerrables() {
    $$('[data-cerrable]').forEach((alerta) => {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'alerta__cerrar';
        boton.setAttribute('aria-label', 'Cerrar mensaje');
        boton.textContent = '×';
        boton.addEventListener('click', () => alerta.remove());
        alerta.prepend(boton);
    });
}

/* 3) Confirmación antes de acciones destructivas ------------------------- */
function confirmaciones() {
    $$('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirmar)) e.preventDefault();
        });
    });
}

/* 4) Validación en el cliente con mensajes en español -------------------- */
function mensajeDe(campo) {
    const v = campo.validity;
    const etiqueta = campo.labels && campo.labels[0] ? campo.labels[0].textContent.replace('*', '').trim() : 'Este campo';
    if (v.valueMissing) return campo.type === 'radio' ? 'Seleccione una opción.' : `«${etiqueta}» es obligatorio.`;
    if (v.typeMismatch && campo.type === 'email') return 'Escriba un correo válido, p. ej. nombre@correo.com.';
    if (v.tooShort) return `Escriba al menos ${campo.minLength} caracteres.`;
    if (v.tooLong) return `Use como máximo ${campo.maxLength} caracteres.`;
    if (v.rangeUnderflow) return `El valor mínimo es ${campo.min}.`;
    if (v.rangeOverflow) return `El valor máximo es ${campo.max}.`;
    if (v.stepMismatch) return 'Revise los decimales del valor.';
    if (v.badInput) return 'Escriba un número válido.';
    if (v.patternMismatch) {
        if (campo.type === 'password') return 'Use al menos 8 caracteres, con letras y números.';
        if (campo.type === 'tel') return 'Use solo números, espacios, + y guiones (mínimo 8).';
        return 'El formato no es válido.';
    }
    return campo.validationMessage;
}

function reglasExtra(campo) {
    campo.setCustomValidity('');
    if (campo.dataset.igualA) {
        const otro = document.getElementById(campo.dataset.igualA);
        if (otro && campo.value !== otro.value) campo.setCustomValidity('Las contraseñas no coinciden.');
    }
    if (campo.dataset.maxDe) {
        const ref = document.getElementById(campo.dataset.maxDe);
        if (ref && campo.value !== '' && ref.value !== '' && Number(campo.value) > Number(ref.value)) {
            campo.setCustomValidity('Los azúcares no pueden superar los carbohidratos.');
        }
    }
}

function mostrarError(campo, mensaje) {
    const contenedor = campo.closest('.campo') || campo.parentElement;
    let nodo = contenedor.querySelector(':scope > .campo__error');
    if (!mensaje) {
        campo.classList.remove('campo__control--error');
        campo.removeAttribute('aria-invalid');
        if (nodo) nodo.remove();
        return;
    }
    if (!nodo) {
        nodo = document.createElement('span');
        nodo.className = 'campo__error';
        nodo.id = (campo.id || campo.name.replace(/\W/g, '')) + '-error';
        contenedor.appendChild(nodo);
    }
    nodo.textContent = mensaje;
    campo.classList.add('campo__control--error');
    campo.setAttribute('aria-invalid', 'true');
    campo.setAttribute('aria-describedby', nodo.id);
}

function validacionFormularios() {
    $$('form[data-validar]').forEach((form) => {
        form.setAttribute('novalidate', '');
        const revisar = (campo) => {
            reglasExtra(campo);
            mostrarError(campo, campo.checkValidity() ? '' : mensajeDe(campo));
            return campo.validity.valid;
        };
        form.addEventListener('submit', (e) => {
            const invalidos = $$('input, select, textarea', form).filter((c) => c.willValidate && !revisar(c));
            if (invalidos.length) {
                e.preventDefault();
                invalidos[0].focus();
            }
        });
        form.addEventListener('input', (e) => {
            if (e.target.classList.contains('campo__control--error')) revisar(e.target);
        });
        form.addEventListener('focusout', (e) => {
            if (e.target.matches('.campo__control') && e.target.value !== '') revisar(e.target);
        });
    });
}

/* 5) Contador de caracteres en textareas --------------------------------- */
function contadoresCaracteres() {
    $$('textarea[data-contador][maxlength]').forEach((area) => {
        const salida = document.createElement('span');
        salida.className = 'campo__ayuda';
        salida.setAttribute('aria-live', 'polite');
        area.insertAdjacentElement('afterend', salida);
        const actualizar = () => { salida.textContent = `${area.value.length} / ${area.maxLength}`; };
        area.addEventListener('input', actualizar);
        actualizar();
    });
}

/* 6) Mostrar u ocultar la contraseña ------------------------------------- */
function verClave() {
    $$('[data-ver-clave]').forEach((casilla) => {
        const campo = document.getElementById(casilla.dataset.verClave);
        casilla.addEventListener('change', () => { campo.type = casilla.checked ? 'text' : 'password'; });
    });
}

/* 7) Registro: secciones según el tipo de condición ---------------------- */
function camposPorCondicion() {
    const radios = $$('input[data-condicion]');
    if (!radios.length) return;
    const bloques = $$('[data-mostrar-si]');
    const aplicar = () => {
        const elegido = (radios.find((r) => r.checked) || {}).value || '';
        bloques.forEach((b) => {
            const visible = b.dataset.mostrarSi.split(' ').includes(elegido);
            b.hidden = !visible;
            $$('input', b).forEach((i) => { i.disabled = !visible; });
        });
    };
    radios.forEach((r) => r.addEventListener('change', aplicar));
    aplicar();
}

/* 8) Chips: marca visualmente la casilla seleccionada -------------------- */
function chips() {
    $$('label.chip input[type="checkbox"]').forEach((casilla) => {
        casilla.addEventListener('change', () => casilla.closest('.chip').classList.toggle('chip--activo', casilla.checked));
    });
}

/* 9) Recetas: agregar y quitar filas de ingredientes --------------------- */
function filasIngredientes() {
    const contenedor = document.querySelector('[data-filas]');
    const agregar = document.querySelector('[data-agregar-fila]');
    if (!contenedor || !agregar) return;
    agregar.addEventListener('click', () => {
        const nueva = contenedor.querySelector('[data-fila]').cloneNode(true);
        nueva.querySelector('select').value = '0';
        nueva.querySelector('input').value = '';
        contenedor.appendChild(nueva);
        nueva.querySelector('select').focus();
    });
    contenedor.addEventListener('click', (e) => {
        const boton = e.target.closest('[data-quitar-fila]');
        if (!boton) return;
        const filas = $$('[data-fila]', contenedor);
        if (filas.length === 1) {
            filas[0].querySelector('select').value = '0';
            filas[0].querySelector('input').value = '';
        } else {
            boton.closest('[data-fila]').remove();
        }
    });
}

/* 10) Producto: recalcular nutrientes para otra cantidad ----------------- */
function calculadoraNutricional() {
    $$('[data-calculadora]').forEach((caja) => {
        const porcion = Number(caja.dataset.porcion);
        const entrada = caja.querySelector('[data-cantidad]');
        const celdas = $$('[data-valor]', caja);
        entrada.addEventListener('input', () => {
            const cantidad = Number(entrada.value);
            if (!cantidad || cantidad < 0 || !porcion) return;
            celdas.forEach((c) => {
                c.textContent = `${formato(Number(c.dataset.valor) * cantidad / porcion)} ${c.dataset.unidad}`;
            });
        });
    });
}

/* 11) Receta: vista previa de nutrientes por porción --------------------- */
function nutricionPorPorcion() {
    const salida = document.querySelector('[data-por-porcion]');
    if (!salida) return;
    const form = salida.closest('form');
    const calcular = () => {
        const porciones = Number(form.querySelector('#porciones').value);
        const carbos = Number(form.querySelector('#carbohidratos').value);
        const azucar = Number(form.querySelector('#azucares').value);
        salida.textContent = porciones > 0 && (carbos || azucar)
            ? `Vista previa: ${formato(carbos / porciones)} g de carbohidratos y ${formato(azucar / porciones)} g de azúcares por porción.`
            : '';
    };
    form.addEventListener('input', calcular);
    calcular();
}

/* 12) Perfil nutricional: barra deslizante sincronizada con el número ---- */
function rangosSincronizados() {
    $$('input[type="range"][data-sincroniza]').forEach((barra) => {
        const numero = document.getElementById(barra.dataset.sincroniza);
        barra.addEventListener('input', () => { numero.value = barra.value; });
        numero.addEventListener('input', () => { if (numero.value !== '') barra.value = numero.value; });
    });
}

/* 13) Filtro instantáneo de filas o bloques ------------------------------ */
function filtroInstantaneo() {
    $$('[data-filtrar]').forEach((entrada) => {
        const destino = document.getElementById(entrada.dataset.filtrar);
        if (!destino) return;
        const elementos = $$('[data-filtrable]', destino);
        const aviso = destino.querySelector('#sin-coincidencias');
        const contador = document.getElementById(entrada.dataset.contador);
        const limpiar = document.getElementById(entrada.dataset.limpiar);
        const normalizar = (t) => t.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const actualizar = () => {
            const q = normalizar(entrada.value.trim());
            let visibles = 0;
            elementos.forEach((el) => {
                el.hidden = q !== '' && !normalizar(el.textContent).includes(q);
                if (!el.hidden) visibles++;
            });
            if (aviso) aviso.hidden = q === '' || elementos.length === 0 || visibles > 0;
            if (contador) contador.textContent = `Mostrando ${visibles} de ${elementos.length} sustituciones.`;
            if (limpiar) limpiar.disabled = entrada.value === '';
        };
        entrada.addEventListener('input', actualizar);
        if (limpiar) {
            limpiar.addEventListener('click', () => {
                entrada.value = '';
                actualizar();
                entrada.focus();
            });
        }
        actualizar();
    });
}

/* 14) Panel de filtros colapsable en móviles ----------------------------- */
function panelFiltrosMovil() {
    $$('[data-alternar]').forEach((boton) => {
        const panel = document.getElementById(boton.dataset.alternar);
        if (!panel) return;
        const movil = window.matchMedia('(max-width: 900px)');
        const aplicar = () => panel.classList.toggle('filtros--colapsado', movil.matches && boton.getAttribute('aria-expanded') !== 'true');
        boton.addEventListener('click', () => {
            const abierto = boton.getAttribute('aria-expanded') === 'true';
            boton.setAttribute('aria-expanded', String(!abierto));
            boton.textContent = abierto ? 'Mostrar filtros' : 'Ocultar filtros';
            aplicar();
        });
        movil.addEventListener('change', aplicar);
        aplicar();
    });
}

/* 15) Enviar el formulario al cambiar el orden --------------------------- */
function autoEnviar() {
    $$('select[data-autoenviar]').forEach((s) => s.addEventListener('change', () => s.form.submit()));
}

/* 16) Modal (moderación: rechazar aporte con motivo) --------------------- */
function modales() {
    let anterior = null;
    const cerrar = (modal) => {
        modal.classList.remove('modal--abierto');
        if (anterior) anterior.focus();
    };
    $$('[data-abrir-modal]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const modal = document.getElementById(boton.dataset.abrirModal);
            if (!modal) return;
            anterior = boton;
            const id = modal.querySelector('[data-modal-id]');
            const titulo = modal.querySelector('[data-modal-titulo]');
            if (id) id.value = boton.dataset.aporte || '';
            if (titulo) titulo.textContent = boton.dataset.titulo || '';
            modal.classList.add('modal--abierto');
            const primero = modal.querySelector('textarea, input:not([type="hidden"]), button');
            if (primero) primero.focus();
        });
    });
    $$('.modal').forEach((modal) => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('[data-cerrar-modal]')) cerrar(modal);
        });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') $$('.modal--abierto').forEach(cerrar);
    });
}

/* 17) Imprimir la lista de compras --------------------------------------- */
function imprimir() {
    $$('[data-imprimir]').forEach((b) => b.addEventListener('click', () => window.print()));
}
