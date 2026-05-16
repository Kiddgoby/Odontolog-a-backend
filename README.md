# Odontología Backend

Sistema de gestión para clínicas dentales desarrollado con Symfony. Proporciona una API para el control de pacientes, citas y odontogramas.

## 🛠️ Tecnologías
- **Framework:** Symfony 7.4
- **Lenguaje:** PHP 8.2
- **Base de Datos:** MySQL 8.0
- **ORM:** Doctrine

## 📊 Estructura de Datos
El sistema gestiona las siguientes entidades principales:
- **Pacientes:** Información personal e historial.
- **Odontólogos:** Especialistas de la clínica.
- **Citas:** Gestión de turnos y consultas.
- **Odontogramas:** Registro del estado dental.
- **Documentos:** Archivos y pruebas médicas.
- **Tratamientos y Patologías:** Catálogo de servicios médicos.

## 🚀 Instalación y Configuración (Guía para Profesores)

Para que el proyecto funcione correctamente en un entorno local (como XAMPP), siga estos pasos:

1. **Instalar dependencias:**
   ```bash
   composer install
   ```

2. **Configurar la conexión:**
   Asegúrese de que el archivo `.env` tenga la URL correcta. El nombre de la base de datos debe ser `dentalclinic_database`:
   ```bash
   DATABASE_URL="mysql://root:@127.0.0.1:3306/dentalclinic_database?serverVersion=8.0"
   ```

3. **Preparar la Base de Datos:**
   Ejecute estos comandos en orden para crear la estructura y cargar los datos de prueba:
   ```bash
   # 1. Crear la base de datos física
   php bin/console doctrine:database:create

   # 2. Crear las tablas (Ignorando migraciones incrementales)
   php bin/console doctrine:schema:update --force

   # 3. Cargar los datos de prueba (Pacientes, citas, odontogramas, etc.)
   php bin/console app:populate-data
   ```

4. **Iniciar el servicio:**
   ```bash
   symfony serve
   ```

---
© 2026 Gestión Dental
