<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Adopt an existing installation created by the legacy SQL script.
        if (Schema::hasTable('roles')) return;
        Schema::create('roles', function (Blueprint $t) { $t->increments('id_rol'); $t->string('nombre', 40)->unique(); $t->string('descripcion', 200)->nullable(); $t->string('estado', 10)->default('Activo'); });
        Schema::create('usuarios', function (Blueprint $t) { $t->increments('id_usuario'); $t->unsignedInteger('id_rol'); $t->string('nombre', 100); $t->string('correo', 120)->unique(); $t->string('password_hash', 255); $t->string('estado', 10)->default('Activo'); $t->timestamp('fecha_creacion')->useCurrent(); $t->foreign('id_rol')->references('id_rol')->on('roles'); });
        Schema::create('parcelas', function (Blueprint $t) { $t->increments('id_parcela'); $t->string('nombre', 80)->unique(); $t->string('ubicacion', 150)->nullable(); $t->text('descripcion')->nullable(); $t->string('estado', 10)->default('Activa'); $t->timestamp('fecha_registro')->useCurrent(); });
        Schema::create('controles_tierra', function (Blueprint $t) { $t->increments('id_control_tierra'); $t->unsignedInteger('id_parcela'); $t->date('fecha_control'); $t->decimal('ph', 4, 2)->nullable(); $t->decimal('humedad', 5, 2)->nullable(); $t->decimal('nitrogeno', 8, 2)->nullable(); $t->decimal('fosforo', 8, 2)->nullable(); $t->decimal('potasio', 8, 2)->nullable(); $t->text('observaciones')->nullable(); $t->foreign('id_parcela')->references('id_parcela')->on('parcelas'); $t->index('fecha_control', 'idx_tierra_fecha'); });
        Schema::create('siembras', function (Blueprint $t) { $t->increments('id_siembra'); $t->unsignedInteger('id_parcela'); $t->string('variedad_uva', 80); $t->date('fecha_siembra'); $t->unsignedInteger('cantidad_plantas'); $t->string('estado', 20)->default('En crecimiento'); $t->text('observaciones')->nullable(); $t->foreign('id_parcela')->references('id_parcela')->on('parcelas'); $t->index('fecha_siembra', 'idx_siembra_fecha'); });
        Schema::create('controles_siembra', function (Blueprint $t) { $t->increments('id_control_siembra'); $t->unsignedInteger('id_siembra'); $t->date('fecha_control'); $t->string('estado_general', 10); $t->boolean('riego_realizado')->default(false); $t->boolean('fertilizacion_realizada')->default(false); $t->text('observaciones')->nullable(); $t->foreign('id_siembra')->references('id_siembra')->on('siembras'); $t->index('fecha_control', 'idx_control_siembra_fecha'); });
        Schema::create('siembras_enfermas', function (Blueprint $t) { $t->increments('id_siembra_enferma'); $t->unsignedInteger('id_siembra'); $t->string('nombre_enfermedad', 120); $t->date('fecha_deteccion'); $t->unsignedInteger('cantidad_plantas_afectadas'); $t->string('nivel_gravedad', 10); $t->text('tratamiento')->nullable(); $t->string('estado', 20)->default('Detectada'); $t->text('observaciones')->nullable(); $t->foreign('id_siembra')->references('id_siembra')->on('siembras'); $t->index('fecha_deteccion', 'idx_enfermedad_fecha'); });
        Schema::create('producciones', function (Blueprint $t) { $t->increments('id_produccion'); $t->unsignedInteger('id_parcela'); $t->string('nombre_vino', 100); $t->string('variedad_uva', 80); $t->date('fecha_inicio'); $t->date('fecha_fin')->nullable(); $t->decimal('cantidad_uva_kg', 12, 2)->default(0); $t->decimal('cantidad_vino_litros', 12, 2)->default(0); $t->string('estado', 15)->default('Planificada'); $t->text('observaciones')->nullable(); $t->foreign('id_parcela')->references('id_parcela')->on('parcelas'); $t->index('fecha_inicio', 'idx_produccion_fecha'); $t->index('estado', 'idx_produccion_estado'); });
        Schema::create('fases_produccion', function (Blueprint $t) { $t->increments('id_fase'); $t->string('nombre', 80)->unique(); $t->text('descripcion')->nullable(); $t->unsignedInteger('orden_fase')->unique(); $t->string('estado', 10)->default('Activa'); });
        Schema::create('seguimiento_fases', function (Blueprint $t) { $t->increments('id_seguimiento'); $t->unsignedInteger('id_produccion'); $t->unsignedInteger('id_fase'); $t->timestamp('fecha_inicio'); $t->timestamp('fecha_fin')->nullable(); $t->string('estado', 15)->default('Pendiente'); $t->text('observaciones')->nullable(); $t->foreign('id_produccion')->references('id_produccion')->on('producciones'); $t->foreign('id_fase')->references('id_fase')->on('fases_produccion'); $t->index('id_produccion', 'idx_seguimiento_produccion'); });
        Schema::create('catas', function (Blueprint $t) { $t->increments('id_cata'); $t->unsignedInteger('id_produccion'); $t->date('fecha_cata'); $t->string('aroma', 200)->nullable(); $t->string('sabor', 200)->nullable(); $t->string('color', 100)->nullable(); $t->string('cuerpo', 100)->nullable(); $t->decimal('puntuacion', 4, 2); $t->string('resultado', 20); $t->text('observaciones')->nullable(); $t->foreign('id_produccion')->references('id_produccion')->on('producciones'); $t->index('fecha_cata', 'idx_cata_fecha'); });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['catas', 'seguimiento_fases', 'fases_produccion', 'producciones', 'siembras_enfermas', 'controles_siembra', 'siembras', 'controles_tierra', 'parcelas', 'usuarios', 'roles'] as $table) Schema::dropIfExists($table);
        Schema::enableForeignKeyConstraints();
    }
};
