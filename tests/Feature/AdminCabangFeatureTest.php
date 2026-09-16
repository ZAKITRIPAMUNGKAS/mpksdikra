<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminCabangFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function adminCabang(string $pcm = 'Tawangmangu'): User
    {
        return User::factory()->create([
            'role' => 'admin_cabang',
            'status' => 'aktif',
            'pcm' => $pcm,
        ]);
    }

    private function anggotaCabang(string $pcm = 'Tawangmangu', string $status = 'menunggu_verifikasi'): Anggota
    {
        $user = User::factory()->create([
            'role' => 'anggota',
            'status' => $status,
            'pcm' => $pcm,
        ]);

        return Anggota::factory()->create([
            'user_id' => $user->id,
            'pcm' => $pcm,
            'kecamatan' => $pcm,
        ]);
    }

    public function test_admin_cabang_dashboard_renders_with_branch_metrics(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $this->anggotaCabang('Tawangmangu', 'menunggu_verifikasi');
        $this->anggotaCabang('Colomadu', 'menunggu_verifikasi'); // Cabang lain

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Cabang Tawangmangu');
        $response->assertSee('PCM Tawangmangu');
        $response->assertSee('Panel Kerja Admin Cabang');
        $response->assertSee('Unduh Data Cabang (Excel)');
        $response->assertDontSee('Buat Event Baru');
    }

    public function test_admin_cabang_only_sees_members_of_their_pcm(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggotaTawangmangu = $this->anggotaCabang('Tawangmangu');
        $anggotaColomadu = $this->anggotaCabang('Colomadu');

        $response = $this->actingAs($admin)->get(route('admin.anggota.index'));

        $response->assertOk();
        $response->assertSee($anggotaTawangmangu->nama_lengkap);
        $response->assertDontSee($anggotaColomadu->nama_lengkap);
        $response->assertSee('Pangkalan Data Kader PCM Tawangmangu');
        $response->assertDontSee('Impor Excel');
    }

    public function test_admin_cabang_can_verify_their_pcm_member(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggota = $this->anggotaCabang('Tawangmangu', 'menunggu_verifikasi');

        $response = $this->actingAs($admin)->post(route('admin.anggota.verify', $anggota));

        $response->assertRedirect(route('admin.anggota.show', $anggota));
        $this->assertEquals('aktif', $anggota->fresh()->user->status);
    }

    public function test_admin_cabang_cannot_verify_member_from_another_pcm(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggotaColomadu = $this->anggotaCabang('Colomadu', 'menunggu_verifikasi');

        $response = $this->actingAs($admin)->post(route('admin.anggota.verify', $anggotaColomadu));

        $response->assertForbidden();
        $this->assertEquals('menunggu_verifikasi', $anggotaColomadu->fresh()->user->status);
    }

    public function test_admin_cabang_cannot_view_detail_of_member_from_another_pcm(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggotaColomadu = $this->anggotaCabang('Colomadu');

        $response = $this->actingAs($admin)->get(route('admin.anggota.show', $anggotaColomadu));

        $response->assertForbidden();
    }

    public function test_admin_cabang_can_reject_their_pcm_member_with_notes(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggota = $this->anggotaCabang('Tawangmangu', 'menunggu_verifikasi');

        $response = $this->actingAs($admin)->post(route('admin.anggota.reject', $anggota), [
            'catatan_verifikasi' => 'Pasfoto kurang rapi, harap unggah foto berjas atau berbatik.',
        ]);

        $response->assertRedirect(route('admin.anggota.show', $anggota));
        $this->assertEquals('ditolak', $anggota->fresh()->user->status);
        $this->assertEquals('Pasfoto kurang rapi, harap unggah foto berjas atau berbatik.', $anggota->fresh()->catatan_verifikasi);
    }

    public function test_admin_cabang_can_export_their_branch_members_to_excel(): void
    {
        Excel::fake();

        $admin = $this->adminCabang('Tawangmangu');
        $this->anggotaCabang('Tawangmangu', 'aktif');

        $response = $this->actingAs($admin)->get(route('admin.anggota.export'));

        $response->assertOk();
        Excel::matchByRegex();
        Excel::assertDownloaded('/^Data_Kader_PCM_Tawangmangu_.*\.xlsx$/');
    }

    public function test_admin_cabang_is_forbidden_from_staff_management(): void
    {
        $admin = $this->adminCabang('Tawangmangu');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_cabang_is_forbidden_from_creating_events(): void
    {
        $admin = $this->adminCabang('Tawangmangu');

        $response = $this->actingAs($admin)->get(route('admin.events.create'));

        $response->assertForbidden();
    }

    public function test_admin_cabang_can_delete_their_pcm_member(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggota = $this->anggotaCabang('Tawangmangu', 'aktif');

        $response = $this->actingAs($admin)
            ->delete(route('admin.anggota.destroy', $anggota));

        $response->assertRedirect(route('admin.anggota.index'))
            ->assertSessionHas('success');

        $this->assertNull(Anggota::find($anggota->id));
    }

    public function test_admin_cabang_cannot_delete_member_from_another_pcm(): void
    {
        $admin = $this->adminCabang('Tawangmangu');
        $anggotaOther = $this->anggotaCabang('Colomadu', 'aktif');

        $response = $this->actingAs($admin)
            ->delete(route('admin.anggota.destroy', $anggotaOther));

        $response->assertForbidden();
        $this->assertNotNull(Anggota::find($anggotaOther->id));
    }
}
