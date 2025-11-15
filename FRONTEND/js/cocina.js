// FRONTEND/js/cocina.js

const API_URL = 'http://127.0.0.1:8000/api/v1';

let empleado = null;

document.addEventListener('DOMContentLoaded', () => {
  empleado = JSON.parse(localStorage.getItem('empleado'));

  // Si no hay sesión, fuera
  if (!empleado) {
    window.location.href = './login.html';
    return;
  }

  // (Opcional) Asegurarnos que solo cocinero entra aquí
  const rol = empleado.nombre_rol?.toLowerCase();
  if (rol !== 'cocinero') {
    alert('No tienes permiso para ver esta pantalla.');
    window.location.href = './login.html';
    return;
  }

  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', () => {
    localStorage.clear();
    window.location.href = './login.html';
  });

  // Cargar pedidos al inicio
  cargarPedidosCocina();

  // Refrescar automáticamente cada 10 segundos
  setInterval(cargarPedidosCocina, 10000);
});

async function cargarPedidosCocina() {
  try {
    const res = await fetch(`${API_URL}/pedidos-cocina`);
    if (!res.ok) {
      console.error('No se pudieron cargar los pedidos de cocina');
      return;
    }

    const pedidos = await res.json();
    renderPedidos(pedidos);
  } catch (err) {
    console.error('Error cargando pedidos de cocina:', err);
  }
}

function renderPedidos(pedidos) {
  const cont = document.getElementById('listaPedidos');
  cont.innerHTML = '';

  if (!pedidos.length) {
    cont.innerHTML = '<p>No hay pedidos pendientes 👌</p>';
    return;
  }

  pedidos.forEach(p => {
    const card = document.createElement('article');
    card.classList.add('pedido-card');

    const estadoClase =
      p.estado === 'Pendiente' ? 'estado-pendiente' : 'estado-preparacion';

    const fecha = p.fecha_pedido ?? '';

    card.innerHTML = `
      <div class="pedido-header">
        <div class="pedido-mesa">Mesa ${p.mesa?.numero_mesa ?? p.id_mesa}</div>
        <span class="pedido-estado ${estadoClase}">
          ${p.estado}
        </span>
      </div>

      <div class="pedido-info">
        <div>Mesero: ${p.empleado?.nombre ?? ''} ${p.empleado?.apellido ?? ''}</div>
        <div>Hora: ${fecha}</div>
      </div>

      <ul class="pedido-items">
        ${ (p.detalles || []).map(det => `
          <li>
            <span>${det.cantidad} x ${det.platillo?.nombre ?? 'Producto ' + det.id_platillo}</span>
            <span>$${det.subtotal}</span>
          </li>
        `).join('') }
      </ul>

      <div class="pedido-actions">
        ${p.estado === 'Pendiente' ? `
          <button class="btn-action btn-tomar">Tomar pedido</button>
        ` : `
          <button class="btn-action btn-tomar" disabled>En preparación</button>
        `}
        <button class="btn-action btn-listo">Marcar listo</button>
      </div>
    `;

    const btnTomar = card.querySelector('.btn-tomar');
    const btnListo = card.querySelector('.btn-listo');

    if (btnTomar && !btnTomar.disabled) {
      btnTomar.addEventListener('click', () => actualizarEstadoPedido(p.id_pedido, 'En preparación'));
    }

    btnListo.addEventListener('click', () => actualizarEstadoPedido(p.id_pedido, 'Listo'));

    cont.appendChild(card);
  });
}

async function actualizarEstadoPedido(idPedido, nuevoEstado) {
  try {
    const res = await fetch(`${API_URL}/pedidos/${idPedido}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ estado: nuevoEstado })
    });

    if (!res.ok) {
      const errData = await res.json().catch(() => ({}));
      alert(errData.message || 'No se pudo actualizar el estado del pedido');
      return;
    }

    // Recargar lista
    cargarPedidosCocina();
  } catch (err) {
    console.error('Error actualizando estado del pedido:', err);
    alert('Error al cambiar estado del pedido');
  }
}
