// FRONTEND/js/admin_categorias.js
const API_URL = 'http://127.0.0.1:8000/api/v1';

let empleado = null;
let categorias = [];
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', () => {
  empleado = JSON.parse(localStorage.getItem('empleado'));

  if (!empleado) {
    window.location.href = './login.html';
    return;
  }

  // Validar rol administrador
  const rol = empleado.nombre_rol?.toLowerCase();
  if (rol !== 'administrador') {
    alert('No tienes permiso para ver esta pantalla.');
    window.location.href = './login.html';
    return;
  }

  document.getElementById('logoutBtn').addEventListener('click', () => {
    localStorage.clear();
    window.location.href = './login.html';
  });

  document.getElementById('btnVolver').addEventListener('click', () => {
    window.location.href = './admin.html';
  });

  document.getElementById('formCategoria').addEventListener('submit', onSubmitForm);
  document.getElementById('btnNuevo').addEventListener('click', resetFormulario);

  cargarCategorias();
});

/* ==============================
   CARGAR LISTA
============================== */

async function cargarCategorias() {
  try {
    const res = await fetch(`${API_URL}/categorias`);
    if (!res.ok) {
      console.error('No se pudieron cargar las categorías');
      return;
    }

    categorias = await res.json();
    renderCategorias();
  } catch (err) {
    console.error('Error cargando categorías:', err);
  }
}

function renderCategorias() {
  const tbody = document.getElementById('tbodyCategorias');
  tbody.innerHTML = '';

  if (!categorias.length) {
    tbody.innerHTML = '<tr><td colspan="3">No hay categorías registradas.</td></tr>';
    return;
  }

  categorias.forEach(cat => {
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td>${cat.id_categoria}</td>
      <td>${cat.nombre_categoria}</td>
      <td>
        <button class="btn-accion btn-edit">Editar</button>
        <button class="btn-accion" style="background:#c0392b;">Eliminar</button>
      </td>
    `;

    const [btnEdit, btnDel] = tr.querySelectorAll('.btn-accion');

    btnEdit.addEventListener('click', () => cargarEnFormulario(cat));
    btnDel.addEventListener('click', () => eliminarCategoria(cat.id_categoria));

    tbody.appendChild(tr);
  });
}

/* ==============================
   FORMULARIO
============================== */

function cargarEnFormulario(cat) {
  modoEdicion = true;
  document.getElementById('formTitulo').textContent = 'Editar categoría';

  document.getElementById('categoriaId').value = cat.id_categoria;
  document.getElementById('nombre_categoria').value = cat.nombre_categoria;

  limpiarMensajeError();
}

function resetFormulario() {
  modoEdicion = false;
  document.getElementById('formTitulo').textContent = 'Crear categoría';
  document.getElementById('categoriaId').value = '';
  document.getElementById('formCategoria').reset();
  limpiarMensajeError();
}

function limpiarMensajeError() {
  const msg = document.getElementById('mensajeError');
  msg.textContent = '';
}

/* ==============================
   GUARDAR (CREAR / EDITAR)
============================== */

async function onSubmitForm(e) {
  e.preventDefault();
  limpiarMensajeError();

  const id = document.getElementById('categoriaId').value;
  const nombre = document.getElementById('nombre_categoria').value.trim();

  if (!nombre) {
    mostrarError('El nombre es obligatorio.');
    return;
  }

  const payload = { nombre_categoria: nombre };

  const url = id
    ? `${API_URL}/categorias/${id}`
    : `${API_URL}/categorias`;

  const method = id ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();

    if (!res.ok) {
      // Intentar mostrar mensajes de validación
      if (data.errors?.nombre_categoria) {
        mostrarError(data.errors.nombre_categoria.join(', '));
      } else {
        mostrarError(data.message || 'Error al guardar la categoría');
      }
      return;
    }

    alert(id ? 'Categoría actualizada correctamente' : 'Categoría creada correctamente');

    await cargarCategorias();
    resetFormulario();

  } catch (err) {
    console.error('Error guardando categoría:', err);
    mostrarError('Error al guardar la categoría');
  }
}

/* ==============================
   ELIMINAR
============================== */

async function eliminarCategoria(id) {
  if (!confirm('¿Seguro que deseas eliminar esta categoría?')) return;

  try {
    const res = await fetch(`${API_URL}/categorias/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
      },
    });

    const data = await res.json();

    if (!res.ok) {
      alert(data.message || 'No se pudo eliminar la categoría');
      return;
    }

    alert('Categoría eliminada correctamente');
    await cargarCategorias();

    const formId = document.getElementById('categoriaId').value;
    if (formId && Number(formId) === id) {
      resetFormulario();
    }

  } catch (err) {
    console.error('Error eliminando categoría:', err);
    alert('Error al eliminar la categoría');
  }
}

/* ==============================
   AYUDAS
============================== */

function mostrarError(texto) {
  const msg = document.getElementById('mensajeError');
  msg.textContent = texto;
}
