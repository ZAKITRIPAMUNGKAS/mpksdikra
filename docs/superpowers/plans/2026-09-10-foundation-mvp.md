# Foundation MVP Implementation Plan

> **For agentic workers:** Use `executing-plans` or `subagent-driven-development` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bangun fondasi Arqam — auth + role, master anggota (daftar mandiri + verifikasi admin), landing page MPKSDI, portal shell.

**Architecture:** Laravel 12 monolith, Blade + Tailwind 4 + Alpine. Tiga area: publik (landing, daftar anggota), admin (verifikasi, kelola), anggota (profil, BA). Auth custom via Laravel `Auth` facade — tanpa Breeze/Jetstream.

**Tech Stack:** Laravel 12, PHP 8.2, MariaDB, Blade, Tailwind CSS 4, Alpine.js, Vite, PHPUnit 11.

**Spec:** `docs/agentic/PRD.md`, `docs/agentic/FORM-ANGGOTA.md`

## Global Constraints

- PHP `^8.2`, Laravel `^12.0`, Tailwind `^4.0`, Vite `^6.0`.
- Role: `admin`, `anggota`, `fasilitator`. Simpan sebagai string kolom `role` di `users`.
- Status anggota: `menunggu_verifikasi`, `aktif`, `nonaktif`.
- NIK unik. Nomor WA normalisasi format `62...`.
- Data sensitif (NIK, WA, alamat) tidak masuk log/test fixture nyata.
- Semua aksi admin wajib otorisasi server-side.
- Bahasa UI: Indonesia.
- Form anggota mobile-first, 4 tab, label besar.

## File Structure

- `database/migrations/` — skema
- `app/Models/` — User, Anggota, Organisasi, AnggotaOrganisasi
- `app/Http/Controllers/` — Auth, Landing, Anggota (publik), Admin, Portal
- `app/Http/Requests/` — validasi form
- `app/Http/Middleware/` — EnsureRole, EnsureAnggotaAktif
- `resources/views/layouts/` — app, admin, portal, publik
- `resources/views/auth/`, `landing/`, `anggota/`, `admin/`, `portal/`
- `routes/web.php`
- `tests/Feature/`

---

### Task 1: Role + User helpers + admin seeder

**Files:**
- Create: `database/migrations/2026_09_10_000001_add_role_and_status_to_users_table.php`
- Modify: `app/Models/User.php`
- Create: `database/seeders/AdminSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/UserRoleTest.php`

**Interfaces:**
- Produces: `User::isAdmin(): bool`, `User::isAnggota(): bool`, `User::isFasilitator(): bool`, `User::ROLES` const.

- [ ] **Step 1: Failing test**

```php
<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $anggota = User::factory()->create(['role' => 'anggota']);
        $fas = User::factory()->create(['role' => 'fasilitator']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isAnggota());
        $this->assertTrue($anggota->isAnggota());
        $this->assertTrue($fas->isFasilitator());
    }

    public function test_default_role_is_anggota(): void
    {
        $u = User::factory()->create();
        $this->assertSame('anggota', $u->fresh()->role);
    }
}
```

- [ ] **Step 2: Run, expect FAIL**

Run: `php artisan test --filter=UserRoleTest`

- [ ] **Step 3: Migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('anggota')->after('password');
            $table->string('status', 30)->default('aktif')->after('role');
            $table->string('no_wa', 20)->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('no_wa');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'no_wa', 'verified_at']);
        });
    }
};
```

- [ ] **Step 4: User helpers**

Tambah ke `app/Models/User.php`:

```php
public const ROLES = ['admin', 'anggota', 'fasilitator'];

public function isAdmin(): bool { return $this->role === 'admin'; }
public function isAnggota(): bool { return $this->role === 'anggota'; }
public function isFasilitator(): bool { return $this->role === 'fasilitator'; }
public function isAktif(): bool { return $this->status === 'aktif'; }
```

Tambah `'role','status','no_wa','verified_at'` ke `$fillable`, cast `verified_at` => `datetime`.

- [ ] **Step 5: Seeder**

`database/seeders/AdminSeeder.php`:

```php
<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@mpksdi.test')],
            [
                'name' => 'Admin MPKSDI',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ubah-segera')),
                'role' => 'admin',
                'status' => 'aktif',
                'verified_at' => now(),
            ]
        );
    }
}
```

Daftarkan di `DatabaseSeeder::run()`.

- [ ] **Step 6: Run tests, expect PASS**

Run: `php artisan test --filter=UserRoleTest`

- [ ] **Step 7: Commit**

```bash
git add database/migrations database/seeders app/Models/User.php tests/Feature/UserRoleTest.php
git commit -m "feat(auth): role + status kolom users, helper, admin seeder"
```

---

### Task 2: Auth (login, logout, middleware role)

**Files:**
- Create: `app/Http/Controllers/Auth/LoginController.php`
- Create: `app/Http/Controllers/Auth/LogoutController.php`
- Create: `app/Http/Middleware/EnsureRole.php`
- Modify: `bootstrap/app.php`
- Create: `resources/views/auth/login.blade.php`
- Create: `resources/views/layouts/guest.blade.php`
- Test: `tests/Feature/AuthTest.php`

**Interfaces:**
- Consumes: `User::isAdmin()`, `User::isAnggota()`, `User::isFasilitator()`.
- Produces: route names `login`, `logout`. Middleware alias `role`.

- [ ] **Step 1: Failing test**

```php
<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_admin(): void
    {
        $u = User::factory()->create([
            'email' => 'admin@x.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $r = $this->post('/login', ['email' => 'admin@x.test', 'password' => 'rahasia123']);
        $r->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($u);
    }

    public function test_login_blocked_for_pending(): void
    {
        User::factory()->create([
            'email' => 'p@x.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'anggota',
            'status' => 'menunggu_verifikasi',
        ]);

        $r = $this->post('/login', ['email' => 'p@x.test', 'password' => 'rahasia123']);
        $r->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_route_denied_for_anggota(): void
    {
        $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $this->actingAs($u)->get('/admin/dashboard')->assertForbidden();
    }
}
```

- [ ] **Step 2: Run, expect FAIL**

Run: `php artisan test --filter=AuthTest`

- [ ] **Step 3: LoginController**

```php
<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->isAktif()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Akun belum diverifikasi admin.',
            ]);
        }

        if ($user->isAdmin() || $user->isFasilitator()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('portal.dashboard'));
    }
}
```

- [ ] **Step 4: LogoutController**

```php
<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
```

- [ ] **Step 5: EnsureRole middleware**

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403);
        }
        if (! $user->isAktif()) {
            abort(403, 'Akun belum aktif.');
        }
        return $next($request);
    }
}
```

- [ ] **Step 6: Register middleware + routes**

`bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureRole::class,
    ]);
})
```

`routes/web.php` minimal:

```php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin,fasilitator'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
});

Route::middleware(['auth', 'role:anggota'])->prefix('portal')->group(function () {
    Route::get('/dashboard', fn () => view('portal.dashboard'))->name('portal.dashboard');
});
```

- [ ] **Step 7: Views minimal**

`resources/views/layouts/guest.blade.php` + `auth/login.blade.php` + `admin/dashboard.blade.php` + `portal/dashboard.blade.php` — placeholder Tailwind, satu layout `layouts/app.blade.php` untuk auth-required.

- [ ] **Step 8: Run tests, expect PASS**

Run: `php artisan test --filter=AuthTest`

- [ ] **Step 9: Commit**

```bash
git commit -am "feat(auth): login, logout, role middleware, route shell"
```

---

### Task 3: Skema master anggota

**Files:**
- Create: `database/migrations/2026_09_10_000010_create_anggota_table.php`
- Create: `database/migrations/2026_09_10_000011_create_organisasi_table.php`
- Create: `database/migrations/2026_09_10_000012_create_anggota_organisasi_table.php`
- Create: `app/Models/Anggota.php`
- Create: `app/Models/Organisasi.php`
- Create: `app/Models/AnggotaOrganisasi.php`
- Test: `tests/Feature/AnggotaModelTest.php`

**Interfaces:**
- Produces: `Anggota` model relasi `user()` belongsTo, `organisasi()` belongsToMany via `anggota_organisasi`. `Organisasi` kategori enum `pdm,pcm,prm,aum,ortom,lain`.

**Catatan:** Field mengikuti `FORM-ANGGOTA.md` persis. Kolom multi-pilih disimpan sebagai JSON (cast array) untuk kesederhanaan.

- [ ] **Step 1: Failing test**

```php
public function test_anggota_has_user_and_organisasi(): void
{
    $u = User::factory()->create(['role' => 'anggota']);
    $a = Anggota::factory()->create(['user_id' => $u->id, 'nik' => '1234567890123456']);

    $this->assertTrue($a->user->is($u));
    $this->assertSame('1234567890123456', $a->nik);

    $o = Organisasi::create(['nama' => 'PRM Mangkubumen', 'kategori' => 'prm']);
    $a->organisasi()->attach($o->id, ['jabatan' => 'Ketua', 'periode' => '2025-2030']);

    $this->assertCount(1, $a->fresh()->organisasi);
}
```

- [ ] **Step 2: Migrations**

`anggota`: id, user_id (FK, unique), nama_lengkap, alamat_rumah, jenis_kelamin enum(L,P), nik string(20) unique, nbm nullable, jabatan_aum nullable, tempat_lahir, tanggal_lahir, umur integer nullable, status_pernikahan, desa_kelurahan, kecamatan, kabupaten, no_wa, email, foto nullable, pendidikan_sd nullable, bahasa_dikuasai json nullable, kemampuan_baca_quran nullable, hafalan_quran nullable, aktivitas_sholat_masjid nullable, aktivitas_kajian_agama nullable, jumlah_buku_agama nullable, sumber_info_muhammadiyah json nullable, langganan_suara_muhammadiyah boolean nullable, lembaga_zis json nullable, tokoh_berpengaruh nullable, alasan_pilih_tokoh text nullable, keaktifan_muhammadiyah json nullable, keaktifan_ortom json nullable, organisasi_lain nullable, harapan_pcm text nullable, harapan_ba text nullable, timestamps, softDeletes.

`organisasi`: id, nama, kategori enum, pimpinan nullable, timestamps.

`anggota_organisasi`: id, anggota_id FK, organisasi_id FK, jabatan nullable, periode nullable, timestamps, unique(anggota_id, organisasi_id, jabatan).

- [ ] **Step 3: Models + factories**

`Anggota` fillable lengkap, cast JSON arrays + tanggal_lahir date. Factory minimal (nik random, nama faker).

- [ ] **Step 4: Run migrate + test**

Run: `php artisan migrate && php artisan test --filter=AnggotaModelTest`

- [ ] **Step 5: Commit**

---

### Task 4: Form daftar anggota (publik) + verifikasi admin

**Files:**
- Create: `app/Http/Controllers/AnggotaRegistrationController.php`
- Create: `app/Http/Requests/AnggotaRegistrationRequest.php`
- Create: `app/Http/Controllers/Admin/AnggotaVerificationController.php`
- Create: `app/Http/Controllers/Admin/AnggotaController.php`
- Create: `resources/views/anggota/register.blade.php`
- Create: `resources/views/admin/anggota/index.blade.php`
- Create: `resources/views/admin/anggota/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AnggotaRegistrationTest.php`

**Interfaces:**
- Consumes: `Anggota`, `User`, `EnsureRole`.
- Produces: route names `anggota.register`, `anggota.register.store`, `admin.anggota.index`, `admin.anggota.verify`.

- [ ] **Step 1: Failing test**

```php
public function test_public_can_register_pending_account(): void
{
    $payload = [
        'nama_lengkap' => 'Budi Santoso, S.T.',
        'nik' => '1234567890123456',
        'jenis_kelamin' => 'L',
        'no_wa' => '081234567890',
        'email' => 'budi@example.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'alamat_rumah' => 'Jl. Contoh 1',
        'desa_kelurahan' => 'Mangkubumen',
        'kecamatan' => 'Banjarsari',
        'kabupaten' => 'Surakarta',
        'tempat_lahir' => 'Surakarta',
        'tanggal_lahir' => '1990-01-01',
        'status_pernikahan' => 'Menikah',
        'foto' => UploadedFile::fake()->image('f.jpg', 400, 600),
    ];

    $r = $this->post(route('anggota.register.store'), $payload);
    $r->assertRedirect();
    $this->assertDatabaseHas('users', ['email' => 'budi@example.test', 'status' => 'menunggu_verifikasi', 'role' => 'anggota']);
}

public function test_admin_verifies_anggota(): void
{
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    $u = User::factory()->create(['role' => 'anggota', 'status' => 'menunggu_verifikasi']);
    $a = Anggota::factory()->create(['user_id' => $u->id]);

    $this->actingAs($admin)->post(route('admin.anggota.verify', $a))
        ->assertRedirect();
    $this->assertSame('aktif', $u->fresh()->status);
}
```

- [ ] **Step 2: Run, expect FAIL**

- [ ] **Step 3: Request validation**

`AnggotaRegistrationRequest` — rules sesuai field wajib form. NIK `digits_between:16,20|unique:anggota,nik`. no_wa regex `/^0?8\d{7,13}$/`. foto `image|max:2048`.

- [ ] **Step 4: Controller store**

Buat user (role anggota, status menunggu_verifikasi, no_wa normalisasi `62...`), buat `Anggota`, simpan foto ke `storage/anggota/`. Redirect sukses dengan pesan "menunggu verifikasi admin".

- [ ] **Step 5: Admin verify**

Route `POST admin/anggota/{anggota}/verify` → set user status `aktif`, `verified_at = now()`. Route `POST .../reject` → set `nonaktif`. Log sederhana ke `activity_logs` (tabel baru) atau skip dulu — **ponytail: skip activity_logs, add when audit dibutuhkan**.

- [ ] **Step 6: Views**

Form 4 tab pakai Alpine (`x-data="{ tab: 'diri' }"`). Label besar `text-lg`, input `py-4 text-lg`. Tombol submit besar.

Admin index: tabel + filter status + tombol verifikasi.

- [ ] **Step 7: Run tests, expect PASS**

- [ ] **Step 8: Commit**

---

### Task 5: Landing page MPKSDI Karanganyar

**Files:**
- Create: `app/Http/Controllers/LandingController.php`
- Create: `resources/views/landing/index.blade.php`
- Create: `resources/views/layouts/public.blade.php`
- Modify: `routes/web.php`

**Interfaces:**
- Produces: route name `landing` di `/`.

- [ ] **Step 1: Route + controller**

```php
Route::get('/', [LandingController::class, 'index'])->name('landing');
```

- [ ] **Step 2: View**

Hero (nama + tagline MPKSDI Karanganyar), section profil singkat, program, kontak. CTA: "Daftar Anggota" → `anggota.register`, "Masuk" → `login`. Pakai `frontend-design` skill saat polish.

- [ ] **Step 3: Smoke test**

```php
public function test_landing_renders(): void
{
    $this->get('/')->assertOk()->assertSee('MPKSDI');
}
```

- [ ] **Step 4: Commit**

---

### Task 6: Profil anggota (portal)

**Files:**
- Create: `app/Http/Controllers/Portal/ProfileController.php`
- Create: `resources/views/portal/profile.blade.php`
- Create: `resources/views/portal/dashboard.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PortalProfileTest.php`

- [ ] **Step 1: Failing test** — anggota aktif bisa lihat + update profil tanpa verifikasi ulang.

- [ ] **Step 2-4: Controller + view + pass**

- [ ] **Step 5: Commit**

---

## Phase 2 (plan terpisah, setelah Phase 1 hijau)

- BA: `event`, `event_peserta`, enroll dari master anggota
- Sesi + presensi QR (generate QR, scan, verifikasi HMAC)
- Pretest/posttest (soal, jawaban, auto-grade, timer)
- Psikomotor + afektif + angket + RTL
- Nilai akhir + ranking urut
- Output: ID Card PDF, sertifikat PDF, laporan Excel/PDF
- WhatsApp & SIMPEKA placeholder

Materi/soal/rubrik/angket diambil dari `D:\website\SKRIPSI\SISTEM` — ekstrak ke seeder saat Phase 2 mulai.

---

## Verification

```bash
php artisan migrate:fresh --seed
php artisan test
npm run build
php artisan serve
```

Cek manual: landing → daftar → login gagal (pending) → verifikasi admin → login berhasil → portal dashboard → edit profil.