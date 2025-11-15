// FRONTEND/js/mesas.js

const API_URL = 'http://127.0.0.1:8000/api/v1';

document.addEventListener('DOMContentLoaded', async () => {
  const empleado = JSON.parse(localStorage.getItem('empleado'));

  // Si no hay empleado logueado, redirige al login
  if (!empleado) {
    window.location.href = './login.html';
    return;
  }

  const mesasContainer = document.getElementById('mesasContainer');
  const logoutBtn = document.getElementById('logoutBtn');

  logoutBtn.addEventListener('click', () => {
    localStorage.clear();
    window.location.href = './login.html';
  });

  try {
    // Llamar al backend para traer las mesas
    const response = await fetch(`${API_URL}/mesas`);
    const mesas = await response.json();

    mesasContainer.innerHTML = ''; // Limpiar

    mesas.forEach(mesa => {
      const div = document.createElement('div');
      div.classList.add('mesa-card');

      // Colores según estado
      if (mesa.estado.toLowerCase() === 'disponible') div.classList.add('disponible');
      else if (mesa.estado.toLowerCase() === 'ocupada') div.classList.add('ocupada');
      else div.classList.add('reservada');

      div.innerHTML = `
        <h3>Mesa ${mesa.numero_mesa}</h3>
        <p>Capacidad: ${mesa.capacidad}</p>
        <p>Estado: <span class="estado">${mesa.estado}</span></p>
      `;

      // Cuando se hace clic en una mesa
      div.addEventListener('click', () => {
        manejarClickMesa(mesa, empleado);
      });

      mesasContainer.appendChild(div);
    });

  } catch (error) {
    console.error('Error al cargar mesas:', error);
    mesasContainer.innerHTML = `<p style="color:white;">Error al cargar las mesas 😢</p>`;
  }
});

/**
 * Maneja el flujo al hacer clic en una mesa:
 * 1. Buscar pedido activo
 * 2. Si no hay, crear pedido nuevo
 * 3. Guardar info en localStorage
 * 4. Redirigir a menu.html
 */
async function manejarClickMesa(mesa, empleado) {
  try {
    // 1️⃣ Intentar obtener pedido activo para esa mesa
    let pedido;
    let res = await fetch(`${API_URL}/mesas/${mesa.id_mesa}/pedido-activo`);

    if (res.status === 404) {
      // 2️⃣ No hay pedido activo → crear uno nuevo
      res = await fetch(`${API_URL}/pedidos`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id_mesa: mesa.id_mesa,
          id_empleado: empleado.id_empleado
        })
      });

      if (!res.ok) {
        const errData = await res.json().catch(() => ({}));
        alert('No se pudo crear el pedido: ' + (errData.message || 'Error desconocido'));
        return;
      }

      const data = await res.json();
      pedido = data.pedido;
    } else if (res.ok) {
      // 3️⃣ Ya había un pedido activo
      pedido = await res.json();
    } else {
      alert('Error al consultar el pedido de la mesa');
      return;
    }

    // 4️⃣ Guardar info en localStorage (mesa + pedido)
    localStorage.setItem('mesaSeleccionada', JSON.stringify({
      id_mesa: mesa.id_mesa,
      numero_mesa: mesa.numero_mesa,
      estado: mesa.estado
    }));

    localStorage.setItem('pedido_actual', JSON.stringify({
      id_pedido: pedido.id_pedido,
      id_mesa: mesa.id_mesa,
      numero_mesa: mesa.numero_mesa
    }));

    // 5️⃣ Redirigir al menú
    window.location.href = './menu.html';

  } catch (error) {
    console.error('Error al manejar la mesa seleccionada:', error);
    alert('Ocurrió un error al seleccionar la mesa');
  }
}
