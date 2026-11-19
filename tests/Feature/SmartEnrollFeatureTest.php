<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SmartEnrollFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function anggota(): User
    {
        return User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
    }

    private function makeEvent(User $admin): Event
    {
        return Event::create([
            'nama_event' => 'Baitul Arqam PDM Karanganyar',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'lokasi' => 'Gedung Dakwah Karanganyar',
            'kuota' => 20,
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_match_names_via_text_input(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Muhammad Dahlan', 'pcm' => 'Karanganyar']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.match', $event), [
                'names_text' => "1. Drs. Muhammad Dahlan, M.Pd\n2. Kader Tidak Terdaftar",
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'matched',
            'already_enrolled',
            'unmatched',
            'whatsapp_invite',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['matched']);
        $this->assertSame($a1->id, $data['matched'][0]['anggota']['id']);
        $this->assertCount(1, $data['unmatched']);
        $this->assertStringContainsString('Kader Tidak Terdaftar', $data['whatsapp_invite']);
    }

    public function test_admin_can_match_names_via_file_upload(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Siti Walidah', 'pcm' => 'Colomadu']);

        $csvContent = "Nama Lengkap,Asal PCM\nSiti Walidah,Colomadu\nKader Antah Berantah,Jaten";
        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csvContent);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.match', $event), [
                'file' => $file,
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['matched']);
        $this->assertSame($a1->id, $data['matched'][0]['anggota']['id']);
        $this->assertCount(1, $data['unmatched']);
    }

    public function test_admin_can_preview_csv_without_creating_participants(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $file = UploadedFile::fake()->createWithContent('peserta.csv', implode("\n", [
            'Nama Lengkap,NIK',
            'Siti Walidah,3313581234567890',
            'Siti Walidah,3313581234567890',
            'Ahmad Dahlan,123',
        ]));

        $response = $this->actingAs($admin)->postJson(
            route('admin.events.smart-enroll.preview', $event),
            ['file' => $file],
        );

        $response->assertOk()->assertJsonPath('success', true);
        $response->assertJsonPath('total_rows', 3);
        $response->assertJsonPath('duplicate_rows', 1);
        $response->assertJsonPath('invalid_nik_rows', 1);
        $this->assertDatabaseCount('event_peserta', 0);
    }

    public function test_admin_can_execute_bulk_enroll(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Satu']);

        $u2 = $this->anggota();
        $a2 = Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Kader Dua']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.execute', $event), [
                'anggota_ids' => [$a1->id, $a2->id],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'enrolled_count' => 2]);

        $this->assertSame(2, EventPeserta::where('event_id', $event->id)->count());
        $this->assertDatabaseHas('event_peserta', [
            'event_id' => $event->id,
            'anggota_id' => $a1->id,
            'status_aktif' => true,
        ]);
        $this->assertNotNull(EventPeserta::where('event_id', $event->id)->where('anggota_id', $a1->id)->value('qr_token'));
    }

    public function test_admin_can_undo_only_the_batch_created_by_sikader(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $existingUser = $this->anggota();
        $existing = Anggota::factory()->create(['user_id' => $existingUser->id]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $existing->id,
            'qr_token' => Str::random(32),
            'status_aktif' => true,
        ]);

        $newUser = $this->anggota();
        $newMember = Anggota::factory()->create(['user_id' => $newUser->id]);
        $execute = $this->actingAs($admin)->postJson(route('admin.events.smart-enroll.execute', $event), [
            'anggota_ids' => [$newMember->id],
        ]);

        $execute->assertOk()->assertJsonPath('success', true);
        $undo = $this->actingAs($admin)->postJson(route('admin.events.smart-enroll.undo', $event), [
            'undo_token' => $execute->json('undo_token'),
        ]);

        $undo->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('event_peserta', ['event_id' => $event->id, 'anggota_id' => $existing->id]);
        $this->assertDatabaseMissing('event_peserta', ['event_id' => $event->id, 'anggota_id' => $newMember->id]);
    }

    public function test_admin_can_report_an_incorrect_answer_without_persisting_full_nik(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)->postJson(route('admin.events.smart-enroll.feedback', $event), [
            'query' => 'Apakah NIK 3313581234567890 sudah cocok?',
            'sources' => [['title' => 'Data kegiatan', 'snippet' => 'NIK 3313581234567890 ditemukan.']],
            'rating' => 2,
            'reason' => 'Jawaban tidak sesuai.',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('sikader_feedback', [
            'event_id' => $event->id,
            'user_id' => $admin->id,
            'query' => 'Apakah NIK 331358**** sudah cocok?',
            'rating' => 2,
        ]);
        $this->assertDatabaseMissing('sikader_feedback', [
            'query' => 'Apakah NIK 3313581234567890 sudah cocok?',
        ]);
    }

    public function test_sikader_feedback_requires_a_valid_likert_rating(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.feedback', $event), [
                'query' => 'Apakah kuota masih tersedia?',
                'rating' => 6,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');
    }

    public function test_non_admin_cannot_access_smart_enroll(): void
    {
        $anggota = $this->anggota();
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $this->actingAs($anggota)
            ->postJson(route('admin.events.smart-enroll.match', $event), ['names_text' => 'Nama'])
            ->assertForbidden();
    }

    public function test_admin_can_view_sikader_modal_with_contained_scroll_and_page_scroll_lock(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->get(route('admin.events.enroll', $event));

        $response->assertOk();
        $response->assertSee('SiKader');
        $response->assertSee('Asisten Perkaderan MPKSDI');
        $response->assertSee('Konteks kegiatan');
        $response->assertSee('grid grid-cols-2 gap-2.5');
        $response->assertSee('h-[calc(100dvh-1rem)]');
        $response->assertSee('p-2 sm:p-4');
        $response->assertSee('overscroll-contain');
        $response->assertSee("document.body.style.overflow = 'hidden';", false);
        $response->assertSee('unlockPageScroll()');
        $response->assertDontSee('h-[100dvh]');
        $response->assertDontSee('AI by Geraikita');
        $response->assertDontSee('AI Asisten');
        $response->assertDontSee('SiKader AI Engine');
        $response->assertDontSee('Pilih Tindakan Cepat:');
        $response->assertDontSee("window.addEventListener('open-smart-enroll'");
        $response->assertDontSee('$dispatch(\'open-smart-enroll\')');
        $response->assertSee('open-smart-enroll');
        $response->assertSee('smartEnrollChatbot');
    }

    public function test_sikader_modal_exposes_accessible_chat_shell_controls(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->get(route('admin.events.enroll', $event));

        $response->assertOk();
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-label="Percakapan SiKader"', false);
        $response->assertSee('aria-live="polite"', false);
        $response->assertSee('aria-label="Menu pengaturan"', false);
        $response->assertSee('aria-label="Pilih ukuran teks"', false);
        $response->assertSee('aria-label="Ganti tema tampilan"', false);
        $response->assertSee('aria-label="Mulai ulang percakapan"', false);
        $response->assertSee('role="progressbar"', false);
        $response->assertSee('aria-label="Kirim pesan"', false);
        $response->assertSee('Rekomendasi seimbang');
        $response->assertSee('Prioritaskan belum aktif');
        $response->assertSee('Prioritaskan kader AUM');
        $response->assertSee('Tempel daftar nama');
        $response->assertSee('Unggah Excel / CSV');
        $response->assertSee('min-h-11', false);
    }

    public function test_admin_can_get_recommendations(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Putri Colomadu', 'jenis_kelamin' => 'P', 'pcm' => 'Colomadu']);

        $u2 = $this->anggota();
        $a2 = Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Kader Putra Karanganyar', 'jenis_kelamin' => 'L', 'pcm' => 'Karanganyar']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.recommend', $event), [
                'gender' => 'P',
                'pcm' => 'Colomadu',
                'limit' => 5,
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['candidates']);
        $this->assertSame($a1->id, $data['candidates'][0]['id']);
    }

    public function test_admin_can_use_ai_prompt_endpoint_with_local_fallback(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response(['message' => 'Quota exceeded'], 402),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Aisyah Putri', 'jenis_kelamin' => 'P', 'pcm' => 'Jaten']);

        // Test prompt pencarian dengan local fallback
        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'Carikan 5 kader putri dari cabang Jaten',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['candidates']);
        $this->assertSame($a1->id, $data['candidates'][0]['id']);
    }

    public function test_admin_can_use_ai_prompt_endpoint_with_ai_response(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'limit' => 5,
                                'gender' => 'P',
                                'pcm' => 'Jaten',
                                'prioritas_belum_ba' => true,
                                'conversational_reply' => 'Baik, ini rekomendasi 5 kader putri dari cabang Jaten:',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Aisyah Putri', 'jenis_kelamin' => 'P', 'pcm' => 'Jaten']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'Carikan 5 kader putri dari cabang Jaten',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('geraikita_ai', $data['source']);
        $this->assertStringContainsString('Baik, ini rekomendasi', $data['message']);
        $this->assertNotEmpty($data['candidates']);
        $this->assertSame($a1->id, $data['candidates'][0]['id']);
    }

    public function test_ai_prompt_handles_casual_greeting_without_candidate_card(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'hai',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('chat', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertEmpty($data['candidates']);
        $this->assertStringContainsString('Hai juga', $data['message']);
        $this->assertStringContainsString('Ada yang bisa saya bantu hari ini', $data['message']);
        $this->assertStringContainsString('SiKader', $data['message']);
        $this->assertStringContainsString($event->nama_event, $data['message']);
    }

    public function test_ai_prompt_replies_warmly_to_hi_greeting(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'hi',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('chat', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertStringContainsString('Hai juga', $data['message']);
        $this->assertStringContainsString('Ada yang bisa saya bantu hari ini', $data['message']);
    }

    public function test_ai_prompt_replies_warmly_to_hello_greeting(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'hello',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('chat', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertStringNotContainsString('belum ditemukan di pangkalan data', $data['message']);
        $this->assertStringContainsString('SiKader', $data['message']);
        $this->assertStringContainsString('Ada yang bisa saya bantu hari ini', $data['message']);
    }

    public function test_ai_prompt_answers_general_muhammadiyah_and_mpksdi_questions(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        // Test tanya Muhammadiyah
        $r1 = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'apa itu muhammadiyah',
            ]);
        $r1->assertOk();
        $d1 = $r1->json();
        $this->assertSame('info', $d1['intent']);
        $this->assertStringNotContainsString('belum ditemukan di pangkalan data', $d1['message']);
        $this->assertStringContainsString('K.H. Ahmad Dahlan', $d1['message']);
        $this->assertStringContainsString('1912', $d1['message']);

        // Test tanya MPKSDI
        $r2 = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'apa tugas mpksdi',
            ]);
        $r2->assertOk();
        $d2 = $r2->json();
        $this->assertSame('info', $d2['intent']);
        $this->assertStringNotContainsString('belum ditemukan di pangkalan data', $d2['message']);
        $this->assertStringContainsString('Majelis Pembinaan Kader', $d2['message']);
    }

    public function test_ai_prompt_answers_quota_inquiry_with_real_event_rag_data(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin); // kuota = 20

        // Enroll 1 participant
        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Terdaftar']);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $a1->id,
            'status_aktif' => true,
            'qr_token' => 'sample-qr-token',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'Berapa sisa kuota yang tersedia?',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('info', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertEmpty($data['candidates']);
        $this->assertStringContainsString('20', $data['message']); // kuota
        $this->assertStringContainsString('19', $data['message']); // sisa
    }

    public function test_ai_prompt_answers_branch_inquiry_with_real_pcm_stats(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Colomadu 1', 'pcm' => 'Colomadu']);

        $u2 = $this->anggota();
        Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Kader Colomadu 2', 'pcm' => 'Colomadu']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'Ada berapa kader di PCM Colomadu?',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('info', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertEmpty($data['candidates']);
        $this->assertStringContainsString('Colomadu', $data['message']);
        $this->assertStringContainsString('2 orang', $data['message']);
    }

    public function test_ai_prompt_explains_event_and_baitul_arqam_without_treating_query_as_name_search(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        // Pertanyaan "jelaskan soal ba ini" tidak boleh dianggap mencari nama orang
        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'jelaskan soal ba ini',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('info', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertEmpty($data['candidates']);
        $this->assertStringNotContainsString('belum ditemukan di pangkalan data', $data['message']);
        $this->assertStringContainsString($event->nama_event, $data['message']);
        $this->assertStringContainsString('Baitul Arqam', $data['message']);
        $this->assertStringContainsString('Evaluasi Kelulusan', $data['message']);
    }

    public function test_ai_prompt_answers_curriculum_and_evaluation_rubric(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        // Pertanyaan kriteria kelulusan
        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'Bagaimana sistem penilaian dan syarat kelulusan kegiatan ini?',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('info', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertStringContainsString('4 Pilar Evaluasi', $data['message']);
        $this->assertStringContainsString('Kognitif', $data['message']);
        $this->assertStringContainsString('Psikomotor', $data['message']);
        $this->assertStringContainsString('Afektif', $data['message']);
        $this->assertStringContainsString('Sertifikat Resmi MPKSDI', $data['message']);
    }

    public function test_ai_prompt_searches_explicit_cadre_name(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Ahmad Dahlan Al-Kudus']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'cari nama Ahmad Dahlan',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('search', $data['intent']);
        $this->assertTrue($data['needs_candidate_card']);
        $this->assertCount(1, $data['candidates']);
        $this->assertSame($a1->id, $data['candidates'][0]['id']);
    }

    public function test_ai_prompt_returns_rag_citations_sources(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'jelaskan soal ba ini',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('sources', $data);
        $this->assertNotEmpty($data['sources']);
        $this->assertArrayHasKey('id', $data['sources'][0]);
        $this->assertArrayHasKey('title', $data['sources'][0]);
        $this->assertArrayHasKey('badge', $data['sources'][0]);
        $this->assertArrayHasKey('snippet', $data['sources'][0]);
        $this->assertStringContainsString('[1]', $data['message']);
    }

    public function test_ai_prompt_answers_list_participants_query_with_enrolled_cadres(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin, ['kuota' => 20]);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Husain Faqihuddin', 'pcm' => 'Colomadu']);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $a1->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        $u2 = $this->anggota();
        $a2 = Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Dian Fajar Qomarudin', 'pcm' => 'Karanganyar']);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $a2->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'list pesertanya coba',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('info', $data['intent']);
        $this->assertFalse($data['needs_candidate_card']);
        $this->assertStringContainsString('Daftar Peserta Terdaftar', $data['message']);
        $this->assertStringContainsString('Husain Faqihuddin', $data['message']);
        $this->assertStringContainsString('Dian Fajar Qomarudin', $data['message']);
        $this->assertStringContainsString('Colomadu', $data['message']);
        $this->assertStringNotContainsString('Nama "list pesertanya coba" belum ditemukan', $data['message']);
    }

    public function test_ai_prompt_recommends_cadres_most_frequently_participating_in_ba(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        // Kader 1: Ikut 2 event
        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Senior Berpengalaman']);
        $evPast1 = $this->makeEvent($admin, ['nama_event' => 'BA Masa Lalu 1']);
        $evPast2 = $this->makeEvent($admin, ['nama_event' => 'BA Masa Lalu 2']);
        EventPeserta::create(['event_id' => $evPast1->id, 'anggota_id' => $a1->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);
        EventPeserta::create(['event_id' => $evPast2->id, 'anggota_id' => $a1->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);

        // Kader 2: Belum pernah ikut
        $u2 = $this->anggota();
        $a2 = Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Kader Pemula Belum BA']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'tolong bantu aku carikan kader yang paling sering ikut BA',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('recommendation', $data['intent']);
        $this->assertTrue($data['needs_candidate_card']);
        $this->assertNotEmpty($data['candidates']);
        $this->assertSame($a1->id, $data['candidates'][0]['id']);
        $this->assertStringContainsString('paling sering dan berpengalaman mengikuti Baitul Arqam', $data['message']);
    }

    public function test_ai_prompt_recommends_cadres_regular_at_mosque_prayers(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create([
            'user_id' => $u1->id,
            'nama_lengkap' => 'Kader Istiqomah Masjid',
            'aktivitas_sholat_masjid' => 'Selalu sholat 5 waktu di masjid',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'carikan peserta yang sholat 5 waktu di masjid',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('recommendation', $data['intent']);
        $this->assertTrue($data['needs_candidate_card']);
        $this->assertNotEmpty($data['candidates']);
        $this->assertStringContainsString('sholat berjamaah di masjid', $data['message']);
    }

    public function test_ai_prompt_answers_cadre_history_inquiry_with_fuzzy_matching(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create([
            'user_id' => $u1->id,
            'nama_lengkap' => 'Husain Faqihuddin Asy Sarif, S.Pd',
            'pcm' => 'Karanganyar',
            'custom_fields' => [
                'riwayat_ba' => [
                    [
                        'event_id' => 8,
                        'nama_event' => 'Baitul Arqam PCM KARANGANYAR',
                        'lokasi' => 'OMAH NGINEP TAWANGMANGU',
                        'tanggal' => '01/05/2026 - 02/05/2026',
                        'status' => 'Lulus',
                    ],
                ],
            ],
        ]);

        // Pengguna mengetik nama dengan variasi ejaan "husain faqihudin" (1 huruf d)
        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'tolong carikan riwayat perkaderan husain faqihudin',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('search', $data['intent']);
        $this->assertTrue($data['needs_candidate_card']);
        $this->assertCount(1, $data['candidates']);
        $this->assertSame($a1->id, $data['candidates'][0]['id']);
        $this->assertStringContainsString('Husain Faqihuddin Asy Sarif, S.Pd', $data['message']);
        $this->assertStringContainsString('Baitul Arqam PCM KARANGANYAR', $data['message']);
        $this->assertStringContainsString('Lulus', $data['message']);
    }

    public function test_ai_prompt_answers_analytics_inquiry_with_risk_detection_and_action_buttons(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        // Buat peserta dan penilaian
        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Uji 1']);
        EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $a1->id, 'qr_token' => 't1', 'status_aktif' => true]);
        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $a1->id,
            'nilai_pretest' => 50,
            'nilai_posttest' => 85,
            'nilai_akhir' => 65, // di bawah KKM 70
            'status_kelulusan' => 'Lulus Bersyarat',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => '/rekap',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('analytics', $data['intent']);
        $this->assertNotEmpty($data['action_buttons']);
        $this->assertStringContainsString('Copilot Analitik Penilaian & Deteksi Risiko 4 Pilar', $data['message']);
        $this->assertStringContainsString('Passing Grade (< 70)', $data['message']);
    }

    public function test_ai_prompt_generates_whatsapp_broadcast_invitation_with_action_button(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'buatkan format undangan wa untuk kegiatan ini',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('whatsapp_invite', $data['intent']);
        $this->assertNotEmpty($data['action_buttons']);
        $this->assertSame('whatsapp', $data['action_buttons'][0]['icon']);
        $this->assertStringContainsString('https://api.whatsapp.com/send?text=', $data['action_buttons'][0]['url']);
        $this->assertStringContainsString('UNDANGAN RESMI BAITUL ARQAM', $data['message']);
    }

    public function test_ai_prompt_provides_quick_export_and_document_links(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => '/export',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('export_docs', $data['intent']);
        $this->assertCount(3, $data['action_buttons']);
        $this->assertStringContainsString('Akses Cepat Unduh Dokumen', $data['message']);
    }

    public function test_ai_prompt_handles_slash_commands_and_direct_action_enroll(): void
    {
        Http::fake([
            'https://ai.geraikita.com/*' => Http::response([], 500),
        ]);

        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $u1 = $this->anggota();
        $a1 = Anggota::factory()->create([
            'user_id' => $u1->id,
            'nama_lengkap' => 'Kader Colomadu Baru',
            'pcm' => 'Colomadu',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
                'prompt' => 'daftarkan 5 kader dari pcm colomadu yang belum ba',
            ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('recommendation', $data['intent']);
        $this->assertTrue($data['needs_candidate_card']);
        $this->assertNotEmpty($data['candidates']);
        $this->assertStringContainsString('Sesuai instruksi Anda', $data['message']);
    }

    public function test_ai_can_prepare_and_confirm_event_update(): void
    {
        Http::fake(['https://ai.geraikita.com/*' => Http::response([], 500)]);
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $originalName = $event->nama_event;

        $preview = $this->actingAs($admin)->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
            'prompt' => 'Ubah lokasi menjadi Gedung Dakwah dan kuota menjadi 40',
        ])->assertOk()->assertJsonPath('intent', 'event_update_confirmation');

        $this->assertDatabaseHas('events', ['id' => $event->id, 'nama_event' => $originalName, 'kuota' => $event->kuota]);
        $token = $preview->json('event_update.token');
        $this->postJson(route('admin.events.smart-enroll.event-update.confirm', $event), ['token' => $token])
            ->assertOk()->assertJsonPath('event.lokasi', 'Gedung Dakwah')->assertJsonPath('event.kuota', 40);
        $this->assertDatabaseHas('events', ['id' => $event->id, 'lokasi' => 'Gedung Dakwah', 'kuota' => 40]);
    }

    public function test_ai_prompt_answers_cbt_soal_clone_inquiry_with_action_buttons(): void
    {
        Http::fake(['https://ai.geraikita.com/*' => Http::response([], 500)]);
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $response = $this->actingAs($admin)->postJson(route('admin.events.smart-enroll.ai-prompt', $event), [
            'prompt' => 'apakah ai sikader bisa membantu menyalin soal dari posttest ke pretest',
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertSame('cbt_soal', $data['intent']);
        $this->assertStringContainsString('Salin & Sinkronisasi Soal CBT', $data['message']);
        $this->assertStringContainsString('N-Gain Score', $data['message']);
        $this->assertNotEmpty($data['action_buttons']);
        $this->assertCount(2, $data['action_buttons']);
        $this->assertSame(route('admin.events.soal.index', [$event, 'pretest']), $data['action_buttons'][0]['url']);
        $this->assertSame(route('admin.events.soal.index', [$event, 'posttest']), $data['action_buttons'][1]['url']);
    }
}
