// FRONTEND/js/admin_mesas.js
const API_URL = 'http://127.0.0.1:8000/api/v1';

let empleado = null;
let sucursales = [];
let mesas = [];
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

  document.getElementById('formMesa').addEventListener('submit', onSubmitForm);
  document.getElementById('btnNuevo').addEventListener('click', resetFormulario);

  document.getElementById('filtroSucursal').addEventListener('change', renderMesas);

  // Cargar datos
  cargarSucursales().then(() => {
    cargarMesas();
  });
});

/* ==============================
   SUCURSALES
============================== */

async function cargarSucursales() {
  try {
    const res = await fetch(`${API_URL}/sucursales`);
    if (!res.ok) {
      console.error('No se pudieron cargar las sucursales');
      return;
    }

    sucursales = await res.json();

    // Llenar select del formulario
    const selectForm = document.getElementById('id_sucursal');
    selectForm.innerHTML = '';
    sucursales.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id_sucursal;
      opt.textContent = s.nombre;
      selectForm.appendChild(opt);
    });

    // Llenar select del filtro
    const filtro = document.getElementById('filtroSucursal');
    filtro.innerHTML = '<option value="">Todas</option>';
    sucursales.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id_sucursal;
      opt.textContent = s.nombre;
      filtro.appendChild(opt);
    });

  } catch (err) {
    console.error('Error cargando sucursales:', err);
  }
}

/* ==============================
   MESAS
============================== */

async function cargarMesas() {
  try {
    const res = await fetch(`${API_URL}/mesas`);
    if (!res.ok) {
      console.error('No se pudieron cargar las mesas');
      return;
    }

    mesas = await res.json();
    renderMesas();
  } catch (err) {
    console.error('Error cargando mesas:', err);
  }
}

function renderMesas() {
  const tbody = document.getElementById('tbodyMesas');
  tbody.innerHTML = '';

  const filtroId = document.getElementById('filtroSucursal').value;

  const lista = filtroId
    ? mesas.filter(m => String(m.id_sucursal) === String(filtroId))
    : mesas;

  if (!lista.length) {
    tbody.innerHTML = '<tr><td colspan="6">No hay mesas registradas.</td></tr>';
    return;
  }

  lista.forEach(m => {
    const tr = document.createElement('tr');

    const sucursal = sucursales.find(s => s.id_sucursal === m.id_sucursal);
    const nombreSucursal = sucursal ? sucursal.nombre : `Sucursal #${m.id_sucursal}`;

    tr.innerHTML = `
      <td>${m.id_mesa}</td>
      <td>${nombreSucursal}</td>
      <td>${m.numero_mesa}</td>
      <td>${m.capacidad}</td>
      <td>${m.estado}</td>
      <td>
        <button class="btn-accion btn-edit">Editar</button>
        <button class="btn-accion" style="background:#c0392b;">Eliminar</button>
      </td>
    `;

    const [btnEdit, btnDel] = tr.querySelectorAll('.btn-accion');

    btnEdit.addEventListener('click', () => cargarEnFormulario(m));
    btnDel.addEventListener('click', () => eliminarMesa(m.id_mesa));

    tbody.appendChild(tr);
  });
}

/* ==============================
   FORMULARIO
============================== */

function cargarEnFormulario(m) {
  modoEdicion = true;
  document.getElementById('formTitulo').textContent = 'Editar mesa';

  document.getElementById('mesaId').value = m.id_mesa;
  document.getElementById('id_sucursal').value = m.id_sucursal;
  document.getElementById('numero_mesa').value = m.numero_mesa;
  document.getElementById('capacidad').value = m.capacidad;
  document.getElementById('estado').value = m.estado;

  limpiarMensajeError();
}

function resetFormulario() {
  modoEdicion = false;
  document.getElementById('formTitulo').textContent = 'Crear mesa';
  document.getElementById('mesaId').value = '';
  document.getElementById('formMesa').reset();
  limpiarMensajeError();

  // Si quieres que por defecto seleccione la primera sucursal:
  if (sucursales.length) {
    document.getElementById('id_sucursal').value = sucursales[0].id_sucursal;
  }
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

  const id = document.getElementById('mesaId').value;
  const payload = {
    id_sucursal: Number(document.getElementById('id_sucursal').value),
    numero_mesa: Number(document.getElementById('numero_mesa').value),
    capacidad: Number(document.getElementById('capacidad').value),
    estado: document.getElementById('estado').value,
  };

  if (!payload.id_sucursal || !payload.numero_mesa || !payload.capacidad) {
    mostrarError('Todos los campos son obligatorios.');
    return;
  }

  const url = id
    ? `${API_URL}/mesas/${id}`
    : `${API_URL}/mesas`;

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
      mostrarError(data.message || 'Error al guardar la mesa');
      console.error('Errores:', data);
      return;
    }

    alert(id ? 'Mesa actualizada correctamente' : 'Mesa creada correctamente');

    await cargarMesas();
    resetFormulario();

  } catch (err) {
    console.error('Error guardando mesa:', err);
    mostrarError('Error al guardar la mesa');
  }
}

/* ==============================
   ELIMINAR
============================== */

async function eliminarMesa(id) {
  if (!confirm('¿Seguro que deseas eliminar esta mesa?')) return;

  try {
    const res = await fetch(`${API_URL}/mesas/${id}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' },
    });

    const data = await res.json();

    if (!res.ok) {
      alert(data.message || 'No se pudo eliminar la mesa');
      return;
    }

    alert('Mesa eliminada correctamente');
    await cargarMesas();

    const formId = document.getElementById('mesaId').value;
    if (formId && Number(formId) === id) {
      resetFormulario();
    }

  } catch (err) {
    console.error('Error eliminando mesa:', err);
    alert('Error al eliminar la mesa');
  }
}

/* ==============================
   AYUDAS
============================== */

function mostrarError(texto) {
  const msg = document.getElementById('mensajeError');
  msg.textContent = texto;
}
