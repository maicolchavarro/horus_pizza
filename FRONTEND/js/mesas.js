// FRONTEND/js/mesas.js
const API_URL = 'http://127.0.0.1:8000/api/v1';

function authHeaders(json = false) {
  const token = sessionStorage.getItem('token');
  const base = token ? { Authorization: `Bearer ${token}` } : {};
  if (json) return { 'Content-Type': 'application/json', Accept: 'application/json', ...base };
  return { Accept: 'application/json', ...base };
}

document.addEventListener('DOMContentLoaded', async () => {
  const empleado = JSON.parse(sessionStorage.getItem('empleado'));

  if (!empleado) {
    window.location.href = './login.html';
    return;
  }

  const mesasContainer = document.getElementById('mesasContainer');
  const userNameEl = document.getElementById('userName');
  const userAvatarEl = document.getElementById('userAvatar');
  const userRoleEl = document.getElementById('userRole');
  const logoutBtn = document.getElementById('logoutBtn');

  if (userNameEl) {
    userNameEl.textContent = `${empleado.nombre ?? ''} ${empleado.apellido ?? ''}`.trim() || 'Mesero';
  }
  if (userRoleEl) {
    userRoleEl.textContent = empleado.nombre_rol || 'Mesero';
  }
  if (userAvatarEl) {
    const inicial = (empleado.nombre?.[0] || empleado.usuario?.[0] || 'M').toUpperCase();
    userAvatarEl.textContent = inicial;
  }

  logoutBtn.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = './login.html';
  });

  try {
    const response = await fetch(`${API_URL}/mesas`, {
      headers: {
        Accept: 'application/json',
        ...authHeaders(),
      },
    });
    if (!response.ok) throw new Error('No autorizado o error al cargar mesas');

    const mesas = await response.json();
    mesasContainer.innerHTML = '';

    mesas.forEach((mesa) => {
      const div = document.createElement('div');
      div.classList.add('mesa-card');

      if (mesa.estado.toLowerCase() === 'disponible') div.classList.add('disponible');
      else if (mesa.estado.toLowerCase() === 'ocupada') div.classList.add('ocupada');
      else div.classList.add('reservada');

      div.innerHTML = `
        <h3>Mesa ${mesa.numero_mesa}</h3>
        <p>Capacidad: ${mesa.capacidad}</p>
        <p>Estado: <span class="estado">${mesa.estado}</span></p>
      `;

      div.addEventListener('click', () => {
        manejarClickMesa(mesa, empleado);
      });

      mesasContainer.appendChild(div);
    });
  } catch (error) {
    console.error('Error al cargar mesas:', error);
    mesasContainer.innerHTML = `<p style="color:#c00;">Error al cargar las mesas</p>`;
  }
});

async function manejarClickMesa(mesa, empleado) {
  try {
    let pedido;
    let res = await fetch(`${API_URL}/mesas/${mesa.id_mesa}/pedido-activo`, {
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...authHeaders(),
      },
    });

    if (res.status === 404) {
      res = await fetch(`${API_URL}/pedidos`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          ...authHeaders(),
        },
        body: JSON.stringify({
          id_mesa: mesa.id_mesa,
          id_empleado: empleado.id_empleado,
        }),
      });

      if (!res.ok) {
        const errData = await res.json().catch(() => ({}));
        alert('No se pudo crear el pedido: ' + (errData.message || 'Error desconocido'));
        return;
      }

      const data = await res.json();
      pedido = data.pedido;
    } else if (res.ok) {
      pedido = await res.json();
    } else {
      alert('Error al consultar el pedido de la mesa');
      return;
    }

    sessionStorage.setItem('mesaSeleccionada', JSON.stringify({
      id_mesa: mesa.id_mesa,
      numero_mesa: mesa.numero_mesa,
      estado: mesa.estado,
    }));

    sessionStorage.setItem('pedido_actual', JSON.stringify({
      id_pedido: pedido.id_pedido,
      id_mesa: mesa.id_mesa,
      numero_mesa: mesa.numero_mesa,
    }));

    window.location.href = './menu.html';
  } catch (error) {
    console.error('Error al manejar la mesa seleccionada:', error);
    alert('Ocurrió un error al seleccionar la mesa');
  }
}
