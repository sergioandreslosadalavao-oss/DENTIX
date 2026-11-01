# Posibles errores por revisar en DENTIX

Esta lista reúne fallos que conviene probar en el sistema. Son casos posibles; marca cada uno solo después de reproducirlo.

## Navegación y redirecciones

- [ ] **Ruta de destino incorrecta:** un enlace o una redirección apunta a un archivo que no existe o cuya ubicación cambió, y termina en un error 404.
- [ ] **Ruta relativa mal calculada:** una ruta funciona desde una carpeta, pero falla al abrir la misma acción desde otra vista.
- [ ] **Redirección después de enviar contenido:** se imprime HTML, texto o espacios antes de `header('Location: ...')`, por lo que PHP no puede enviar la cabecera.
- [ ] **La ejecución continúa después de redirigir:** falta `exit` después de `header()`, y se ejecuta código que debería detenerse.
- [ ] **Destino o flujo equivocado:** después de iniciar/cerrar sesión o guardar un formulario, el usuario termina en una pantalla inesperada o en un ciclo de redirecciones.
- [ ] **Pérdida del formulario al redirigir:** una validación falla, se vuelve al formulario, pero los datos ingresados o el mensaje de error desaparecen.

## Base de datos

- [ ] **Configuración de conexión incorrecta:** host, puerto, nombre de base, usuario o contraseña de `.env` no coinciden con PostgreSQL.
- [ ] **Base o esquema ausente:** existe el servidor, pero falta la base, una tabla, una vista o un tipo utilizado por la aplicación.
- [ ] **Consulta desactualizada:** el código consulta una columna o tabla que no coincide con el esquema instalado.
- [ ] **Restricción de integridad:** una operación viola una clave foránea, una restricción `UNIQUE` o un campo obligatorio y no muestra una explicación comprensible.

## Formularios y operaciones

- [ ] **Campos con nombres incompatibles:** el formulario envía nombres distintos de los que espera el PHP y los valores llegan vacíos.
- [ ] **Validación incompleta:** se aceptan fechas, correos, duraciones, precios o identificadores inválidos.
- [ ] **Identificador ausente o inválido:** editar o eliminar recibe un ID vacío, incorrecto o inexistente y no informa el resultado.
- [ ] **Doble envío:** actualizar la página después de guardar vuelve a enviar el formulario y duplica la operación.
- [ ] **Registro duplicado o relacionado:** se intenta repetir un dato único o eliminar un elemento que todavía está asociado a citas.

## Sesión y acceso

- [ ] **Acceso sin iniciar sesión:** una pantalla administrativa permite consultar o modificar información sin validar la sesión.
- [ ] **Sesión inconsistente:** iniciar sesión no conserva el estado, cerrar sesión no lo invalida o el usuario queda atrapado en redirecciones.
- [ ] **Credenciales incorrectas:** el sistema no distingue entre datos inválidos y un error interno, o revela información sensible en pantalla.

## Recursos y presentación

- [ ] **CSS, JavaScript o imágenes no encontrados:** una ruta relativa incorrecta o diferencias entre mayúsculas y minúsculas hacen que la vista cargue sin sus recursos.
- [ ] **Error PHP oculto o demasiado detallado:** una pantalla queda vacía, o muestra rutas, consultas y datos internos que deberían quedar solo en los registros del servidor.

## Seguridad

- [ ] **Datos sin escapar en HTML:** valores ingresados por usuarios se muestran directamente y pueden alterar la página.
- [ ] **Operación sin protección CSRF:** una acción que modifica datos puede ejecutarse desde una solicitud externa no autorizada.
- [ ] **Contraseña almacenada o comparada sin hash seguro:** las credenciales no se procesan con funciones de hash para contraseñas.
