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

    public function test_ciudadano_puede_consultar_turno_por_documento(): void
    {
        $turno = Turno::create([
            'codigo' => 'TEST-CONSULTA',
            'numero_secuencia' => 991,
            'area_id' => $this->area->id,
            'tramite_id' => $this->tramite->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '44556677',
            'ciudadano_nombre' => 'Consulta DNI Test',
            'es_preferencial' => false,
            'estado' => 'espera',
            'fecha_emision' => now(),
        ]);

        $response = $this->post(route('ciudadano.buscar'), [
            'documento' => '44556677',
        ]);

        $response->assertRedirect(route('ciudadano.seguimiento', 'TEST-CONSULTA'));
    }

    public function test_operador_recibe_turnos_de_kiosko_en_cola_y_puede_atender_todas_las_areas(): void
    {
        // 1. Emitir turno desde el kiosco
        $this->post(route('kiosco.emitir'), [
            'tramite_id' => $this->tramite->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '33445566',
            'ciudadano_nombre' => 'Kiosco a Operador Test',
            'es_preferencial' => 0,
        ]);

        // 2. Operador consulta la cola de su ventanilla
        $response = $this->get(route('operador.cola', $this->ventanilla->id));
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertGreaterThan(0, $data['total_espera']);
        $this->assertTrue(collect($data['cola_espera'])->contains('ciudadano_documento', '33445566'));

        // 3. Crear turno en otra área y verificar que con todas_las_areas se recibe
        $otraArea = Area::create([
            'nombre' => 'Otra Área Test',
            'codigo' => 'OAT',
            'color' => '#123456',
            'activo' => true,
        ]);
        $otroTramite = Tramite::create([
            'area_id' => $otraArea->id,
            'nombre' => 'Trámite Otra Área',
            'tiempo_estimado_minutos' => 10,
            'es_preferencial' => false,
            'activo' => true,
        ]);

        $turnoOtraArea = Turno::create([
            'codigo' => 'OAT-001',
            'numero_secuencia' => 1,
            'area_id' => $otraArea->id,
            'tramite_id' => $otroTramite->id,
            'ciudadano_tipo_documento' => 'DNI',
            'ciudadano_documento' => '88990011',
            'ciudadano_nombre' => 'Ciudadano Otra Área',
            'es_preferencial' => false,
            'estado' => 'espera',
            'fecha_emision' => now(),
        ]);

        // Consulta filtrada (no debería incluir OAT-001 porque la ventanilla es de $this->area)
        $respMiArea = $this->get(route('operador.cola', ['ventanilla' => $this->ventanilla->id, 'todas_las_areas' => 0]));
        $this->assertFalse(collect($respMiArea->json()['cola_espera'])->contains('codigo', 'OAT-001'));

        // Consulta universal (debería incluir OAT-001)
        $respTodas = $this->get(route('operador.cola', ['ventanilla' => $this->ventanilla->id, 'todas_las_areas' => 1]));
        $this->assertTrue(collect($respTodas->json()['cola_espera'])->contains('codigo', 'OAT-001'));

        // 4. Operador puede llamar turno específico
        $respLlamarDirecto = $this->post(route('operador.llamar.especifico', [
            'ventanilla' => $this->ventanilla->id,
            'turno' => $turnoOtraArea->id,
        ]));
        $respLlamarDirecto->assertStatus(200);

        $turnoOtraArea->refresh();
        $this->assertEquals('llamado', $turnoOtraArea->estado);
        $this->assertEquals($this->ventanilla->id, $turnoOtraArea->ventanilla_id);
    }
}
