# MunTurno 🏛️⏱️
### Sistema Web de Turnos Virtuales y Gestión de Colas en Tiempo Real para Atención Ciudadana

[![Laravel 11+](https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![WebSockets](https://img.shields.io/badge/WebSockets-Laravel_Reverb-F05340?style=for-the-badge)](https://laravel.com/docs/reverb)
[![TailwindCSS 4](https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/Tests-8%20Passed%20(100%25)-brightgreen?style=for-the-badge)](tests/Feature/MunTurnoTest.php)

**MunTurno** es una solución integral desarrollada para entidades del sector público y municipalidades, diseñada para optimizar los tiempos de espera del ciudadano, erradicar las filas presenciales desordenadas y proporcionar analítica en tiempo real a los gestores de atención.

---

## 🚀 Características Principales

### 1. 📱 Kiosco de Autoservicio (`/kiosco`)
* Emisión presencial táctil o virtual de tickets de turno por área y trámite.
* **Atención Preferencial:** Asignación automática de prioridad para personas con discapacidad, adultos mayores y mujeres gestantes (prefijo `P-00X`).
* Generación de **Ticket Digital con código QR dinámico** para seguimiento directo en el móvil del ciudadano y formato de impresión térmica.

### 2. 🖥️ Pantalla TV para Sala de Espera (`/tv`)
* Monitor Full HD / 4K para salas de espera en Smart TVs.
* Animación protagonista para el turno llamado actual con resplandor pulsante (*glow effect*).
* **Audio Doble:**
  * *Chime armónico* (campanilla sintetizada con Web Audio API en frecuencias 784Hz y 1046Hz).
  * *Síntesis de voz automática en español* (*Web Speech API*): *"Turno [código], acercarse a [ventanilla]"*.
* Historial de los últimos turnos llamados y cola de próximos ciudadanos en espera.
* Reloj digital en vivo y cintillo de avisos institucionales.

### 3. 👨‍💼 Panel de Operador de Ventanilla (`/operador`)
* Selección dinámica de módulo o ventanilla de atención.
* Botón **Llamar Siguiente** con priorización automática de cola.
* Botón **Re-Llamar** (repite el aviso sonoro y de voz en pantalla y alerta en el celular del ciudadano).
* **Cronómetro en vivo** para auditoría del tiempo exacto de atención ($T_a$).
* Opciones de **Iniciar Atención**, **Finalizar Atención** (con registro de observaciones) y **Marcar Ausente**.
* Visualización en tiempo real de la cola de espera de su área.

### 4. 📲 Seguimiento Móvil del Ciudadano (`/mi-turno/{codigo}`)
* El ciudadano escanea el QR de su ticket o ingresa a su URL única.
* Actualización reactiva mediante WebSockets:
  * Contador decreciente de personas por delante en la fila.
  * Alarma sonora y vibración en el móvil (`navigator.vibrate`) en el instante exacto en que es llamado.
  * Encuesta interactiva de satisfacción de 5 estrellas al finalizar la atención.

### 5. 📊 Dashboard de Supervisión y Métricas (`/admin`)
* Indicadores clave de rendimiento (KPIs) en tiempo real:
  * Total de turnos generados hoy.
  * Total de atendidos, en espera y ausentes.
  * Tiempo medio de espera ($T_e$).
  * Tiempo medio de atención en ventanilla ($T_a$).
* Estado en vivo de todas las ventanillas (Disponible, En Atención, Inactiva) y operador asignado.
* Gráfico de distribución de afluencia por departamentos municipales.

---

## 🛠️ Stack Tecnológico

| Capa | Tecnología |
|---|---|
| **Backend** | Laravel 11+ / PHP 8.5 |
| **Base de Datos** | MySQL (con transacciones atómicas y `lockForUpdate`) |
| **Tiempo Real** | Laravel Reverb (Servidor WebSocket oficial nativo) + Laravel Echo |
| **Frontend** | Blade + Tailwind CSS 4 + Alpine.js |
| **Audio / Voz** | Web Audio API + Web Speech API (`SpeechSynthesis`) |
| **Testing** | PHPUnit (Feature y Unit Tests con `DatabaseTransactions`) |

---

## 📋 Requisitos Previos

* PHP >= 8.2 (con extensiones `pdo_mysql`, `curl`, `mbstring`, `openssl`).
* MySQL >= 8.0 o MariaDB >= 10.4 (incluido en XAMPP).
* Composer >= 2.0.
* Node.js >= 18.x y npm.

---

## ⚙️ Instalación y Puesta en Marcha

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/marxcuadros24-png/MunTurno.git
   cd MunTurno
   ```

2. **Instalar dependencias de PHP y JavaScript:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configurar el archivo `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=munturno_db
   DB_USERNAME=root
   DB_PASSWORD=

   BROADCAST_CONNECTION=reverb
   QUEUE_CONNECTION=sync
   ```

4. **Ejecutar migraciones y seeders de prueba:**
   ```bash
   php artisan migrate --seed
   ```

5. **Iniciar los servicios:**
   * **Servidor WebSockets Reverb:**
     ```bash
     php artisan reverb:start
     ```
   * **Servidor Web:**
     ```bash
     php artisan serve
     ```
   *(O de forma integrada con `composer run dev`)*.

---

## 🌐 URLs de Acceso

| Módulo | Ruta |
|---|---|
| **Portal Central** | `http://localhost:8000/` |
| **Kiosco de Turnos** | `http://localhost:8000/kiosco` |
| **Pantalla TV Sala** | `http://localhost:8000/tv` |
| **Módulo de Operador** | `http://localhost:8000/operador` |
| **Dashboard y Métricas** | `http://localhost:8000/admin` |

---

## 🧪 Pruebas Automatizadas

Para ejecutar la suite de pruebas unitarias y de integración:

```bash
php artisan test
```

```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\MunTurnoTest
✓ portal home y kiosco cargan correctamente
✓ emision de turno regular y disparo de evento
✓ emision de turno preferencial con prefijo p
✓ operador llama siguiente turno y dispara websocket
✓ operador inicia y finaliza atencion
✓ api pantalla tv y metricas admin

Tests:    8 passed (38 assertions)
Duration: ~0.8s
```

---

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE). Desarrollado para modernizar la atención ciudadana en la gestión pública.
