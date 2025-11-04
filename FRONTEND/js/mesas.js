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
    // Llamar al backend
    const response = await fetch('http://127.0.0.1:8000/api/v1/mesas');
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
        // Guardar en localStorage
        localStorage.setItem('mesaSeleccionada', JSON.stringify({
          id_mesa: mesa.id_mesa,
          numero_mesa: mesa.numero_mesa,
          estado: mesa.estado,
          id_empleado: empleado.id_empleado
        }));

        // Redirigir al menú
        window.location.href = './menu.html';
      });

      mesasContainer.appendChild(div);
    });

  } catch (error) {
    console.error('Error al cargar mesas:', error);
    mesasContainer.innerHTML = `<p style="color:white;">Error al cargar las mesas 😢</p>`;
  }
});
