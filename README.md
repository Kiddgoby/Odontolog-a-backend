# Odontología Backend

Sistema de gestión para clínicas dentales desarrollado con Symfony. Proporciona una API REST para el control de pacientes, citas, odontogramas y documentos clínicos.

## 🛠️ Tecnologías
- **Framework:** Symfony 7.4
- **Lenguaje:** PHP 8.2
- **Base de Datos:** MySQL 8.0
- **ORM:** Doctrine

## 📊 Estructura de Datos
El sistema gestiona las siguientes entidades principales:
- **Pacientes:** Información personal, historial médico y credenciales de acceso.
- **Odontólogos:** Especialistas con horarios y especialidades.
- **Boxes:** Consultorios disponibles en la clínica.
- **Citas:** Gestión de turnos, estado (`pendiente` / `completada`) y asistencia.
- **Odontogramas:** Registro del estado dental por diente y cara, vinculado a una cita.
- **Documentos:** Archivos clínicos (radiografías, informes, presupuestos).
- **Tratamientos y Patologías:** Catálogo de servicios y diagnósticos médicos.
- **Dientes:** Notación FDI (permanentes 11-48, deciduos 51-85).

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

   # 3. Cargar los datos de prueba (pacientes, dentistas, citas, odontogramas, documentos...)
   php bin/console app:populate-data
   ```

4. **Iniciar el servicio:**
   ```bash
   symfony serve
   ```

## 🔑 Credenciales de prueba

| Rol | Email | Contraseña |
|---|---|---|
| Paciente | laura@example.com | password123 |
| Paciente | pedro@example.com | password123 |
| Paciente | ana@example.com | password123 |
| Dentista | juan.perez@example.com | password123 |
| Dentista | maria.garcia@example.com | password123 |
| Dentista | carlos.rod@example.com | password123 |

---
© 2026 Gestión Dental
