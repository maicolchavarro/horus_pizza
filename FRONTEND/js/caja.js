// FRONTEND/js/caja.js
const API_URL = 'http://127.0.0.1:8000/api/v1';

let empleado = null;
let pedidosCaja = [];
let pedidoSeleccionado = null;
let totalSeleccionado = 0;

function authHeaders(json = false) {
  const token = sessionStorage.getItem('token');
  const base = token ? { Authorization: `Bearer ${token}` } : {};
  if (json) return { 'Content-Type': 'application/json', Accept: 'application/json', ...base };
  return { Accept: 'application/json', ...base };
}

document.addEventListener('DOMContentLoaded', () => {
  empleado = JSON.parse(sessionStorage.getItem('empleado'));

  if (!empleado) {
    window.location.href = './login.html';
    return;
  }

  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = './login.html';
  });

  document.getElementById('btnRealizarPago').addEventListener('click', abrirModalPago);
  document.getElementById('btnCancelarPago').addEventListener('click', cerrarModalPago);
  document.getElementById('btnConfirmarPago').addEventListener('click', confirmarPago);

  cargarPedidosParaCaja();
  setInterval(cargarPedidosParaCaja, 10000);
});

async function cargarPedidosParaCaja() {
  try {
    const res = await fetch(`${API_URL}/pedidos-caja`, { headers: authHeaders() });
    if (!res.ok) {
      console.error('No se pudieron cargar los pedidos para caja');
      return;
    }

    pedidosCaja = await res.json();
    renderPedidos();
  } catch (err) {
    console.error('Error cargando pedidos para caja:', err);
  }
}

function renderPedidos() {
  const ul = document.getElementById('listaPedidosCaja');
  ul.innerHTML = '';

  if (!pedidosCaja.length) {
    ul.innerHTML = '<li>No hay pedidos listos para cobrar.</li>';
    limpiarDetalle();
    return;
  }

  pedidosCaja.forEach(p => {
    const li = document.createElement('li');
    li.classList.add('item-pedido');

    const descripcionMesa = p.mesa
      ? `Mesa ${p.mesa.numero_mesa}`
      : `Mesa #${p.id_mesa}`;

    const total = calcularTotalesPedido(p).total;

    li.innerHTML = `
      <div>
        <div><strong>${descripcionMesa}</strong></div>
        <div style="font-size:12px;color:#666;">Pedido #${p.id_pedido}</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:13px;">$${total.toLocaleString()}</div>
        <div style="font-size:11px;color:#999;">${p.fecha_pedido ?? ''}</div>
      </div>
    `;

    li.addEventListener('click', () => {
      document
        .querySelectorAll('.item-pedido')
        .forEach(el => el.classList.remove('activo'));
      li.classList.add('activo');
      seleccionarPedido(p);
    });

    ul.appendChild(li);
  });
}

function seleccionarPedido(pedido) {
  pedidoSeleccionado = pedido;

  const info = document.getElementById('infoPedido');
  const mesaTexto = pedido.mesa
    ? `Mesa ${pedido.mesa.numero_mesa}`
    : `Mesa #${pedido.id_mesa}`;

  info.innerHTML = `
    <p><strong>${mesaTexto}</strong></p>
    <p>Pedido #${pedido.id_pedido}</p>
    <p>Atendido por: ${pedido.empleado?.nombre ?? ''} ${pedido.empleado?.apellido ?? ''}</p>
  `;

  const tbody = document.getElementById('tbodyDetalle');
  tbody.innerHTML = '';

  (pedido.detalles || []).forEach(det => {
    const tr = document.createElement('tr');
    const nombre = det.platillo?.nombre ?? `Platillo #${det.id_platillo}`;
    const precio = Number(det.precio_unitario);
    const subtotal = Number(det.subtotal);

    tr.innerHTML = `
      <td>${nombre}</td>
      <td>${det.cantidad}</td>
      <td>$${precio.toLocaleString()}</td>
      <td>$${subtotal.toLocaleString()}</td>
    `;

    tbody.appendChild(tr);
  });

  const { subtotal, impuesto, total } = calcularTotalesPedido(pedido);
  totalSeleccionado = total;

  document.getElementById('subTotal').textContent = subtotal.toLocaleString();
  document.getElementById('impuesto').textContent = impuesto.toLocaleString();
  document.getElementById('total').textContent = total.toLocaleString();

  document.getElementById('btnRealizarPago').disabled = false;
}

function limpiarDetalle() {
  pedidoSeleccionado = null;
  totalSeleccionado = 0;
  document.getElementById('infoPedido').innerHTML =
    '<p>Selecciona un pedido para ver el detalle.</p>';
  document.getElementById('tbodyDetalle').innerHTML = '';
  document.getElementById('subTotal').textContent = '0';
  document.getElementById('impuesto').textContent = '0';
  document.getElementById('total').textContent = '0';
  document.getElementById('btnRealizarPago').disabled = true;
}

function calcularTotalesPedido(pedido) {
  const subtotal = (pedido.detalles || []).reduce(
    (sum, d) => sum + Number(d.subtotal),
    0
  );

  const tasaIva = 0.00;
  const impuesto = subtotal * tasaIva;
  const total = subtotal + impuesto;

  return { subtotal, impuesto, total };
}

function abrirModalPago() {
  if (!pedidoSeleccionado) {
    alert('Selecciona un pedido primero');
    return;
  }

  document.getElementById('modalTotal').textContent =
    totalSeleccionado.toLocaleString();

  document.getElementById('modalPago').classList.remove('oculto');
}

function cerrarModalPago() {
  document.getElementById('modalPago').classList.add('oculto');
}

async function confirmarPago() {
  if (!pedidoSeleccionado) {
    alert('No hay pedido seleccionado');
    return;
  }

  const metodoPago = document.getElementById('metodoPagoModal').value;

  try {
    const res = await fetch(`${API_URL}/facturas`, {
      method: 'POST',
      headers: authHeaders(true),
      body: JSON.stringify({
        id_pedido: pedidoSeleccionado.id_pedido,
        metodo_pago: metodoPago
      })
    });

    const data = await res.json();

    if (!res.ok) {
      alert(data.message || 'No se pudo registrar el pago');
      return;
    }

    const factura = data.factura;
    alert('Pago realizado y factura generada.');

    const facturaRes = await fetch(`${API_URL}/facturas/${factura.id_factura}`, {
      headers: authHeaders()
    });
    const facturaCompleta = await facturaRes.json();

    cerrarModalPago();
    imprimirTicket(facturaCompleta);

    await cargarPedidosParaCaja();
    limpiarDetalle();

  } catch (err) {
    console.error('Error al confirmar pago:', err);
    alert('Error al procesar el pago');
  }
}

function imprimirTicket(factura) {
  const w = window.open('', '_blank', 'width=300,height=600');

  const fecha = factura.fecha_emision ?? new Date().toLocaleString();
  const mesa = factura.pedido?.mesa
    ? `Mesa ${factura.pedido.mesa.numero_mesa}`
    : `Mesa #${factura.pedido?.id_mesa ?? ''}`;

  const detalles = factura.detalles || [];
  const subtotal = Number(factura.subtotal || 0);
  const impuesto = Number(factura.impuesto || 0);
  const total = Number(factura.total || 0);

  w.document.write(`
    <html>
    <head>
      <title>Ticket</title>
      <style>
        * { font-family: monospace; box-sizing: border-box; }
        body {
          margin: 0;
          padding: 6px;
          width: 80mm;
        }
        .ticket { font-size: 12px; }
        .centro { text-align: center; }
        .linea {
          border-top: 1px dashed #000;
          margin: 4px 0;
        }
        .fila {
          display: flex;
          justify-content: space-between;
        }
        .items {
          margin-top: 4px;
        }
        .items div {
          display: flex;
          justify-content: space-between;
          margin: 2px 0;
        }
      </style>
    </head>
    <body onload="window.print(); setTimeout(() => window.close(), 500);">
      <div class="ticket">
        <div class="centro">
          <strong>HORUS PIZZA</strong><br>
          NIT/ID: 123456789-0<br>
          Cra X #X-XX Tunja<br>
          Tel: 300 000 0000<br>
        </div>

        <div class="linea"></div>

        <div>
          Factura: ${factura.numero_factura}<br>
          Fecha: ${fecha}<br>
          ${mesa}<br>
        </div>

        <div class="linea"></div>

        <div class="items">
          ${detalles.map(d => `
            <div>
              <span>${d.cantidad} x ${d.nombre_platillo}</span>
              <span>$${Number(d.subtotal).toLocaleString()}</span>
            </div>
          `).join('')}
        </div>

        <div class="linea"></div>

        <div class="fila">
          <span>SUBTOTAL</span>
          <span>$${subtotal.toLocaleString()}</span>
        </div>
        <div class="fila">
          <span>IMPUESTO</span>
          <span>$${impuesto.toLocaleString()}</span>
        </div>
        <div class="fila">
          <strong>TOTAL</strong>
          <strong>$${total.toLocaleString()}</strong>
        </div>

        <div class="linea"></div>

        <div class="centro">
          ¡Gracias por su compra!<br>
          Vuelva pronto 🍕
        </div>
      </div>
    </body>
    </html>
  `);

  w.document.close();
}
