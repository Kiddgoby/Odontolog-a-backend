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

## � Instalación

1. Instalar dependencias:
   ```bash
   composer install
   ```

2. Configurar la base de datos en el archivo `.env`:
   ```text
   DATABASE_URL="mysql://root:@127.0.0.1:3306/dentalclinic_database"
   ```

3. Crear la base de datos y ejecutar migraciones:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

4. Iniciar servidor:
   ```bash
   symfony serve
   ```

---
© 2026 Gestión Dental
