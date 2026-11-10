// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  
  // Animación de entrada para el index (tarjetas de módulos)
  const cards = document.querySelectorAll('.card-modulo');
  if (cards.length) {
    gsap.from(cards, {
      opacity: 0,
      y: 30,
      duration: 0.6,
      stagger: 0.15,
      ease: 'power2.out'
    });
  }

  // Animación para filas de tablas en listas
  const filas = document.querySelectorAll('table tbody tr');
  if (filas.length) {
    gsap.from(filas, {
      opacity: 0,
      x: -20,
      duration: 0.4,
      stagger: 0.05,
      ease: 'power1.out'
    });
  }

  // Animación para formularios
  const formGroups = document.querySelectorAll('.form-group');
  if (formGroups.length) {
    gsap.from(formGroups, {
      opacity: 0,
      y: 15,
      duration: 0.5,
      stagger: 0.1,
      ease: 'power2.out'
    });
  }
});