<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Áreas / Departamentos Municipales
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 10)->unique(); // Ej: REN, REG, LIC, OBR
            $table->text('descripcion')->nullable();
            $table->string('color', 20)->default('#2563eb');
            $table->string('icono', 50)->default('building');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. Trámites o Servicios por Área
        Schema::create('tramites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('tiempo_estimado_minutos')->default(10);
            $table->boolean('es_preferencial')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 3. Ventanillas o Módulos de Atención
        Schema::create('ventanillas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50); // Ej: Ventanilla 01, Módulo A
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('ubicacion')->nullable(); // Ej: Hall Central, Piso 1
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['disponible', 'atendiendo', 'en_pausa', 'inactiva'])->default('inactiva');
            $table->timestamps();
        });

        // 4. Turnos Emitidos
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->index(); // Ej: REN-001, P-005
            $table->unsignedInteger('numero_secuencia')->default(1);
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('tramite_id')->constrained('tramites');
            $table->foreignId('ventanilla_id')->nullable()->constrained('ventanillas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('ciudadano_tipo_documento', ['DNI', 'CE', 'PASAPORTE', 'OTRO'])->default('DNI');
            $table->string('ciudadano_documento', 30)->index();
            $table->string('ciudadano_nombre');
            $table->string('ciudadano_telefono', 30)->nullable();
            $table->string('ciudadano_email', 150)->nullable();
            $table->boolean('es_preferencial')->default(false);
            $table->enum('estado', ['espera', 'llamado', 'atendiendo', 'finalizado', 'ausente', 'cancelado'])->default('espera')->index();
            $table->unsignedInteger('llamadas_contador')->default(0);
            $table->timestamp('fecha_emision')->useCurrent();
            $table->timestamp('fecha_primer_llamado')->nullable();
            $table->timestamp('fecha_inicio_atencion')->nullable();
            $table->timestamp('fecha_fin_atencion')->nullable();
            $table->unsignedInteger('tiempo_espera_segundos')->nullable();
            $table->unsignedInteger('tiempo_atencion_segundos')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // 5. Historial de Llamados (para auditar cuántas veces y a qué hora sonó)
        Schema::create('llamados_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->onDelete('cascade');
            $table->foreignId('ventanilla_id')->constrained('ventanillas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('llamado_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llamados_historial');
        Schema::dropIfExists('turnos');
        Schema::dropIfExists('ventanillas');
        Schema::dropIfExists('tramites');
        Schema::dropIfExists('areas');
    }
};
