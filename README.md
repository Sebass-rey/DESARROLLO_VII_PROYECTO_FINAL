Proyecto: LegalSmart - Plataforma B2C de trámites legales digitales

Tecnologías:
- PHP 8 (Laragon)
- MySQL
- HTML, CSS, Bootstrap 5
- Stripe Checkout (modo prueba)

Instalación:
1. Copiar la carpeta "legalsmart" dentro de C:\laragon\www\PROYECTO\
2. Crear la base de datos "legalsmart_db" e importar el archivo SQL incluido (legalsmart_db.sql).
3. Ajustar la ruta base en los enlaces si es necesario (por defecto: http://localhost/PROYECTO/legalsmart/index.php).
4. En "pago.php", reemplazar la clave "sk_test_TU_LLAVE_AQUI" por una Secret Key de Stripe en modo prueba, ya que este proyecto utiliza Stripe en modo **test** para simular pagos.


Accesos:
- Admin:
  Email: admin@legalsmart.com
  Clave: admin123

Descripción funcional:
- Catálogo de servicios legales con precios.
- Registro e inicio de sesión de clientes.
- Creación de trámites con descripción y carga de documentos.
- Seguimiento del trámite mediante timeline de estados.
- Integración con Stripe Checkout (modo pruebas) para pagos.
- Panel de administrador para actualizar el estado de los trámites.
