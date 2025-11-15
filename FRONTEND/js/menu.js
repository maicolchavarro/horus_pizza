// FRONTEND/js/menu.js

const API_URL = 'http://127.0.0.1:8000/api/v1';

let pedidoActual = null;
let categorias = [];
let categoriaActiva = null;

document.addEventListener('DOMContentLoaded', async () => {
  // Pedido creado/obtenido en mesas.js
  pedidoActual = JSON.parse(localStorage.getItem('pedido_actual'));

  if (!pedidoActual) {
    alert('No hay pedido seleccionado.');
    window.location.href = './mesas.html';
    return;
  }

  try {
    await cargarCategorias();
    await cargarBadgeCarrito();
  } catch (err) {
    console.error('Error al inicializar menú:', err);
    alert('Error al cargar el menú. Revisa la consola del navegador.');
  }
});

/* =========================
   CATEGORÍAS
========================= */

async function cargarCategorias() {
  const contTabs = document.getElementById('tabsCategorias');

  const res = await fetch(`${API_URL}/categorias`);
  if (!res.ok) {
    throw new Error('No se pudieron cargar las categorías');
  }

  categorias = await res.json();
  contTabs.innerHTML = '';

  categorias.forEach((cat, index) => {
    const tab = document.createElement('div');
    tab.textContent = cat.nombre_categoria;
    tab.classList.add('tab-cat');

    if (index === 0) {
      tab.classList.add('activo');
      categoriaActiva = cat;
      cargarProductos(cat.id_categoria);
    }

    tab.addEventListener('click', () => {
      document
        .querySelectorAll('.tab-cat')
        .forEach(el => el.classList.remove('activo'));

      tab.classList.add('activo');
      categoriaActiva = cat;
      cargarProductos(cat.id_categoria);
    });

    contTabs.appendChild(tab);
  });
}

/* =========================
   PRODUCTOS POR CATEGORÍA
========================= */

async function cargarProductos(idCategoria) {
  const res = await fetch(`${API_URL}/menu?id_categoria=${idCategoria}`);
  if (!res.ok) {
    console.error('Error al cargar productos de la categoría', idCategoria);
    return;
  }

  const productos = await res.json();
  const lista = document.getElementById('listaProductos');
  lista.innerHTML = '';

  productos.forEach(prod => {
    const li = document.createElement('li');
    li.classList.add('item-producto');

    const rutaImg = prod.imagen
      ? `http://127.0.0.1:8000/${prod.imagen}`
      : '../img/default.jpg';

    li.innerHTML = `
      <img src="${rutaImg}" class="item-img" alt="${prod.nombre}">

      <div class="item-info">
        <div class="item-nombre">${prod.nombre}</div>
        <div class="item-desc">${prod.descripcion ?? ''}</div>
        <div class="item-precio">$${Number(prod.precio).toLocaleString()}</div>
      </div>

      <div class="item-accion">
        <button class="btn-agregar">Agregar</button>
      </div>
    `;

    const btnAgregar = li.querySelector('.btn-agregar');
    btnAgregar.addEventListener('click', (e) => {
      agregarProducto(prod.id_platillo, e.currentTarget);
    });

    lista.appendChild(li);
  });
}

/* =========================
   AGREGAR PRODUCTO
========================= */

async function agregarProducto(idPlatillo, btnElement) {
  try {
    const res = await fetch(`${API_URL}/detalles`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id_pedido: pedidoActual.id_pedido,
        id_platillo: idPlatillo,
        cantidad: 1
      })
    });

    if (!res.ok) {
      const errData = await res.json().catch(() => ({}));
      alert(errData.message || 'Error al agregar producto');
      return;
    }

    // Actualizar badge del carrito
    await cargarBadgeCarrito();

    // Animar botón
    if (btnElement) {
      btnElement.classList.add('btn-added');
      setTimeout(() => btnElement.classList.remove('btn-added'), 180);
    }

    // Animar carrito
    animarCarrito();
  } catch (err) {
    console.error('Error agregando producto', err);
  }
}

/* =========================
   BADGE DEL CARRITO
========================= */

async function cargarBadgeCarrito() {
  try {
    const res = await fetch(`${API_URL}/pedidos/${pedidoActual.id_pedido}/detalle-completo`);
    if (!res.ok) return;

    const pedido = await res.json();
    const cantidadTotal =
      pedido.detalles?.reduce((sum, d) => sum + d.cantidad, 0) || 0;

    const badge = document.getElementById('badgeCarrito');
    if (cantidadTotal > 0) {
      badge.textContent = cantidadTotal;
      badge.classList.remove('oculto');
    } else {
      badge.classList.add('oculto');
    }
  } catch (err) {
    console.error('Error cargando badge carrito', err);
  }
}

/* Animación carrito */
function animarCarrito() {
  const cart = document.getElementById('cartIcon');
  if (!cart) return;

  cart.classList.remove('cart-bump');
  void cart.offsetWidth; // reinicia la animación
  cart.classList.add('cart-bump');
}

/* =========================
   NAVEGACIÓN
========================= */

function irMesas() {
  window.location.href = './mesas.html';
}

function irMenu() {
  // ya estamos aquí
}

function irPedidos() {
  window.location.href = './menupedidos.html';
}
