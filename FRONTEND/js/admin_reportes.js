// FRONTEND/js/admin_reportes.js
const API_URL = 'http://127.0.0.1:8000/api/v1';

let empleado = null;

document.addEventListener('DOMContentLoaded', () => {
  empleado = JSON.parse(localStorage.getItem('empleado'));

  // Si no hay login
  if (!empleado) {
    window.location.href = './login.html';
    return;
  }

  // Solo administrador
  const rol = empleado.nombre_rol?.toLowerCase();
  if (rol !== 'administrador') {
    alert('No tienes permiso para ver esta pantalla.');
    window.location.href = './login.html';
    return;
  }

  // Bienvenida
  const bienvenida = document.getElementById('adminBienvenida');
  if (bienvenida) {
    bienvenida.textContent = `Bienvenido, ${empleado.nombre} ${empleado.apellido}`;
  }

  // Logout
  document.getElementById('logoutBtn').addEventListener('click', () => {
    localStorage.clear();
    window.location.href = './login.html';
  });

  // NAV superior
  document.getElementById('navResumen').addEventListener('click', () => {
    window.location.href = './admin.html';
  });
  document.getElementById('navReportes').addEventListener('click', () => {
    // ya estás aquí
  });
  document.getElementById('navMesas').addEventListener('click', () => {
    window.location.href = './admin_mesas.html';
  });
  document.getElementById('navMenu').addEventListener('click', () => {
    window.location.href = './admin_menu.html';
  });
  document.getElementById('navEmpleados').addEventListener('click', () => {
    window.location.href = './admin_empleados.html';
  });
  document.getElementById('navCategorias').addEventListener('click', () => {
    window.location.href = './admin_categorias.html';
  });

  // Cargar ventas del día al inicio
  cargarVentasDia();

  // Opcional: refrescar cada 30s
  setInterval(cargarVentasDia, 30000);
});

async function cargarVentasDia() {
  try {
    const res = await fetch(`${API_URL}/admin/resumen`);
    if (!res.ok) {
      console.error('No se pudo obtener el resumen de admin');
      return;
    }

    const data = await res.json();
    renderVentasDia(data);
  } catch (err) {
    console.error('Error cargando ventas del día:', err);
  }
}

function renderVentasDia(data) {
  // 1) Total vendido hoy
  const totalHoy = Number(data.ventas_dia ?? 0);
  const ventasEl = document.getElementById('ventasDiaTotal');
  if (ventasEl) {
    ventasEl.textContent = '$' + totalHoy.toLocaleString();
  }

  // 2) Facturas del día (cantidad)
  // Si el backend ya manda facturas_dia, lo usamos; si no, lo calculamos filtrando
  let facturasHoy = [];

  const hoyStr = new Date().toISOString().slice(0, 10); // 'YYYY-MM-DD'
  const ultimas = data.ultimas_facturas || [];

  facturasHoy = ultimas.filter(f => {
    if (!f.fecha_emision) return false;
    // fecha_emision viene como "2025-11-16 20:16:36"
    const soloFecha = String(f.fecha_emision).split(' ')[0];
    return soloFecha === hoyStr;
  });

  const cantidadHoy = data.facturas_dia ?? facturasHoy.length;
  const facturasEl = document.getElementById('facturasDiaTotal');
  if (facturasEl) {
    facturasEl.textContent = Number(cantidadHoy).toString();
  }

  // 3) Pintar tabla con las facturas de hoy filtradas
  const tbody = document.getElementById('tbodyFacturas');
  if (!tbody) return;

  tbody.innerHTML = '';

  if (!facturasHoy.length) {
    tbody.innerHTML = '<tr><td colspan="5">No hay facturas emitidas hoy.</td></tr>';
    return;
  }

  facturasHoy.forEach(f => {
    const tr = document.createElement('tr');

    const fecha = f.fecha_emision ?? '';
    const mesaTexto = f.pedido?.mesa
      ? `Mesa ${f.pedido.mesa.numero_mesa}`
      : (f.pedido ? `Mesa #${f.pedido.id_mesa}` : '-');

    const total = Number(f.total ?? 0);

    tr.innerHTML = `
      <td>${f.numero_factura}</td>
      <td>${fecha}</td>
      <td>${mesaTexto}</td>
      <td>${f.metodo_pago}</td>
      <td>$${total.toLocaleString()}</td>
    `;

    tbody.appendChild(tr);
  });
}
