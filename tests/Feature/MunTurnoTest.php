<?php

namespace Tests\Feature;

use App\Events\TurnoActualizado;
use App\Events\TurnoCreado;
use App\Events\TurnoLlamado;
use App\Models\Area;
use App\Models\Tramite;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MunTurnoTest extends TestCase
{
    use DatabaseTransactions;

    protected Area $area;
    protected Tramite $tramite;
    protected Ventanilla $ventanilla;
    protected User $operador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operador = User::firstOrCreate(
            ['email' => 'operador_test@munturno.gob'],
            ['name' => 'Operador Test', 'password' => bcrypt('password')]
        );

        $this->area = Area::firstOrCreate(
            ['codigo' => 'TEST'],
            [
                'nombre' => 'Área de Prueba',
                'color' => '#2563eb',
                'activo' => true,
            ]
        );

        $this->tramite = Tramite::firstOrCreate(
            ['area_id' => $this->area->id, 'nombre' => 'Trámite de Prueba'],
            [
                'tiempo_estimado_minutos' => 10,
                'es_preferencial' => false,
                'activo' => true,
            ]
        );

        $this->ventanilla = Ventanilla::firstOrCreate(
            ['numero' => 'Ventanilla Test'],
            [
                'area_id' => $this->area->id,
                'ubicacion' => 'Piso 1',
                'user_id' => $this->operador->id,
                'estado' => 'disponible',
            ]
        );
    }

    public function test_portal_home_y_kiosco_cargan_correctamente(): void
    {
        $responseHome = $this->get(route('home'));
        $responseHome->assertStatus(200);
        $responseHome->assertSee('MunTurno');

        $responseKiosco = $this->get(route('kiosco.index'));
        $responseKiosco->assertStatus(200);
        $responseKiosco->assertSee('Solicitud de Turno');
    }

    public function test_emision_de_turno_regular_y_disparo_de_evento(): void
    {
        Event::fake([TurnoCreado::class]);

        $response = $this->post(route('kiosco.emitir'), [
            'tramite_id' => $this->tramite->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '12345678',
            'ciudadano_nombre' => 'Juan Pérez Test',
            'es_preferencial' => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('turnos', [
            'area_id' => $this->area->id,
            'ciudadano_documento' => '12345678',
            'ciudadano_nombre' => 'Juan Pérez Test',
            'es_preferencial' => false,
            'estado' => 'espera',
        ]);

        Event::assertDispatched(TurnoCreado::class);
    }

    public function test_emision_de_turno_preferencial_con_prefijo_p(): void
    {
        Event::fake([TurnoCreado::class]);

        $response = $this->post(route('kiosco.emitir'), [
            'tramite_id' => $this->tramite->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '87654321',
            'ciudadano_nombre' => 'María Anciana Test',
            'es_preferencial' => true,
        ]);

        $response->assertRedirect();

        $turno = Turno::where('ciudadano_documento', '87654321')->latest('id')->first();
        $this->assertNotNull($turno);
        $this->assertTrue((bool)$turno->es_preferencial);
        $this->assertStringStartsWith('P-', $turno->codigo);

        Event::assertDispatched(TurnoCreado::class);
    }

    public function test_operador_llama_siguiente_turno_y_dispara_websocket(): void
    {
        Event::fake([TurnoLlamado::class]);

        // Limpiar cola previa de esta área de prueba para garantizar orden
        Turno::where('area_id', $this->area->id)->delete();

        // Crear turno en espera
        $turno = Turno::create([
            'codigo' => 'TEST-099',
            'numero_secuencia' => 99,
            'area_id' => $this->area->id,
            'tramite_id' => $this->tramite->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '99887766',
            'ciudadano_nombre' => 'Pedro Llamado Test',
            'es_preferencial' => false,
            'estado' => 'espera',
            'fecha_emision' => now(),
        ]);

        $response = $this->post(route('operador.llamar', $this->ventanilla->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $turno->refresh();
        $this->assertEquals('llamado', $turno->estado);
        $this->assertEquals($this->ventanilla->id, $turno->ventanilla_id);
        $this->assertEquals(1, $turno->llamadas_contador);

        Event::assertDispatched(TurnoLlamado::class);
    }

    public function test_operador_inicia_y_finaliza_atencion(): void
    {
        Event::fake([TurnoActualizado::class]);

        $turno = Turno::create([
            'codigo' => 'TEST-100',
            'numero_secuencia' => 100,
            'area_id' => $this->area->id,
            'tramite_id' => $this->tramite->id,
            'ventanilla_id' => $this->ventanilla->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '55443322',
            'ciudadano_nombre' => 'Carlos Atención Test',
            'es_preferencial' => false,
            'estado' => 'llamado',
            'fecha_emision' => now(),
            'fecha_primer_llamado' => now(),
        ]);

        // 1. Iniciar Atención
        $resIniciar = $this->post(route('operador.iniciar', $turno->id));
        $resIniciar->assertStatus(200);
        $turno->refresh();
        $this->assertEquals('atendiendo', $turno->estado);
        $this->assertNotNull($turno->fecha_inicio_atencion);

        // 2. Finalizar Atención
        $resFin = $this->post(route('operador.finalizar', $turno->id), [
            'observaciones' => 'Trámite completado satisfactoriamente',
        ]);
        $resFin->assertStatus(200);
        $turno->refresh();
        $this->assertEquals('finalizado', $turno->estado);
        $this->assertNotNull($turno->fecha_fin_atencion);
        $this->assertNotNull($turno->tiempo_atencion_segundos);

        Event::assertDispatched(TurnoActualizado::class);
    }

    public function test_api_pantalla_tv_y_metricas_admin(): void
    {
        $responseTv = $this->get(route('tv.estado'));
        $responseTv->assertStatus(200);
        $responseTv->assertJsonStructure(['ultimo_llamado', 'turnos_en_atencion', 'historial', 'en_espera', 'total_espera']);

        $responseMetricas = $this->get(route('admin.metricas'));
        $responseMetricas->assertStatus(200);
        $responseMetricas->assertJsonStructure(['total_emitidos', 'total_atendidos', 'total_espera', 'promedio_espera_min']);
    }

    public function test_operador_puede_derivar_turno_a_otra_area(): void
    {
        $nuevaArea = Area::create([
            'nombre' => 'Área Destino Test',
            'codigo' => 'DEST',
            'color' => '#10b981',
            'activo' => true,
        ]);

        $turno = Turno::create([
            'codigo' => 'TEST-101',
            'numero_secuencia' => 101,
            'area_id' => $this->area->id,
            'tramite_id' => $this->tramite->id,
            'ventanilla_id' => $this->ventanilla->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '11223344',
            'ciudadano_nombre' => 'Derivado Test',
            'es_preferencial' => false,
            'estado' => 'atendiendo',
            'fecha_emision' => now(),
            'fecha_inicio_atencion' => now(),
        ]);

        $response = $this->post(route('operador.transferir', $turno->id), [
            'nueva_area_id' => $nuevaArea->id,
            'motivo' => 'Derivado para pago',
        ]);

        $response->assertStatus(200);
        $turno->refresh();

        $this->assertEquals($nuevaArea->id, $turno->area_id);
        $this->assertEquals('espera', $turno->estado);
        $this->assertTrue((bool)$turno->es_preferencial);
        $this->assertNull($turno->ventanilla_id);
        $this->assertStringContainsString('Derivado para pago', $turno->observaciones);
    }

    public function test_admin_puede_descargar_reporte_csv(): void
    {
        $response = $this->get(route('admin.exportar'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}
