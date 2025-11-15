// FRONTEND/js/menupedidos.js
const API_URL = 'http://127.0.0.1:8000/api/v1';

let pedidoActual = null;
let pedidoData = null; // aquí guardamos el pedido completo

document.addEventListener('DOMContentLoaded', async () => {
  pedidoActual = JSON.parse(localStorage.getItem('pedido_actual'));

  if (!pedidoActual) {
    alert('No hay pedido seleccionado');
    window.location.href = './mesas.html';
    return;
  }

  document.getElementById('tituloPedido').textContent =
    `Mesa ${pedidoActual.numero_mesa} - Pedido #${pedidoActual.id_pedido}`;

  document.getElementById('btnEnviar').addEventListener('click', enviarACocina);

  await cargarPedido();
});

async function cargarPedido() {
  try {
    const res = await fetch(`${API_URL}/pedidos/${pedidoActual.id_pedido}/detalle-completo`);
    if (!res.ok) {
      console.error('Error al cargar el pedido');
      return;
    }

    const pedido = await res.json();
    pedidoData = pedido; // guardar para saber estado

    const lista = document.getElementById('listaPedido');
    lista.innerHTML = '';

    (pedido.detalles || []).forEach(det => {
      const li = document.createElement('li');
      li.classList.add('item-detalle');

      li.innerHTML = `
        <div class="item-detalle-info">
          <div class="item-detalle-nombre">${det.platillo.nombre}</div>
          <div class="item-detalle-cantidad">Cantidad: ${det.cantidad}</div>
          <div class="item-precio">$${det.subtotal}</div>
        </div>
        <div class="item-detalle-acciones">
          <button class="btn-mini btn-mas">+1</button>
          <button class="btn-mini btn-menos">-1</button>
          <button class="btn-mini rojo btn-eliminar">Eliminar</button>
        </div>
      `;

      const btnMas = li.querySelector('.btn-mas');
      const btnMenos = li.querySelector('.btn-menos');
      const btnDel = li.querySelector('.btn-eliminar');

      btnMas.addEventListener('click', () =>
        cambiarCantidad(det.id_detalle, det.cantidad + 1)
      );

      btnMenos.addEventListener('click', () => {
        if (det.cantidad - 1 <= 0) {
          eliminarDetalle(det.id_detalle);
        } else {
          cambiarCantidad(det.id_detalle, det.cantidad - 1);
        }
      });

      btnDel.addEventListener('click', () => eliminarDetalle(det.id_detalle));

      lista.appendChild(li);
    });

    document.getElementById('totalPedido').textContent = pedido.total ?? 0;

    // 🔁 Ajustar botón "Enviar a cocina" según el estado
    actualizarBotonEnviar(pedido.estado);

  } catch (err) {
    console.error('Error cargando pedido', err);
  }
}

function actualizarBotonEnviar(estado) {
  const btn = document.getElementById('btnEnviar');

  if (estado === 'Pendiente') {
    btn.disabled = false;
    btn.textContent = 'Enviar a cocina';
  } else if (estado === 'En preparación') {
    btn.disabled = true;
    btn.textContent = 'En preparación...';
  } else if (estado === 'Listo') {
    btn.disabled = true;
    btn.textContent = 'Pedido listo';
  } else if (estado === 'Pagado') {
    btn.disabled = true;
    btn.textContent = 'Pedido pagado';
  } else {
    // cualquier otro estado raro
    btn.disabled = true;
    btn.textContent = estado;
  }
}

async function cambiarCantidad(idDetalle, nuevaCantidad) {
  try {
    await fetch(`${API_URL}/detalles/${idDetalle}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cantidad: nuevaCantidad })
    });

    await cargarPedido();
  } catch (err) {
    console.error('Error cambiando cantidad', err);
  }
}

async function eliminarDetalle(idDetalle) {
  try {
    await fetch(`${API_URL}/detalles/${idDetalle}`, {
      method: 'DELETE'
    });

    await cargarPedido();
  } catch (err) {
    console.error('Error eliminando detalle', err);
  }
}

/**
 * 🟠 Enviar a cocina:
 * - Cambia el estado del pedido a "En preparación"
 * - El backend (PedidoController@update) se encarga de marcar la mesa como "Ocupada"
 */
async function enviarACocina() {
  try {
    const res = await fetch(`${API_URL}/pedidos/${pedidoActual.id_pedido}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ estado: 'En preparación' })
    });

    if (!res.ok) {
      const errData = await res.json().catch(() => ({}));
      alert(errData.message || 'No se pudo enviar el pedido a cocina');
      return;
    }

    alert('Pedido enviado a cocina ✅');

    // Recargar información para ver estado actualizado
    await cargarPedido();

  } catch (err) {
    console.error('Error enviando a cocina', err);
    alert('Error al enviar el pedido a cocina');
  }
}

/* Navegación inferior (si la usas en esta vista) */
function irMesas() {
  window.location.href = './mesas.html';
}
function irMenu() {
  window.location.href = './menu.html';
}
