<?php

namespace App\Http\Controllers;

use App\Models\SbaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SbaSettingController extends Controller
{
    /**
     * Koleksi kurasi foto Unsplash berdasarkan 6 kategori preferensi
     */
    private function getCuratedUnsplashCatalog(): array
    {
        return [
            'workspace' => [
                'label' => 'Workspace',
                'items' => [
                    [
                        'id'    => 'ws-1',
                        'title' => 'Modern Minimal Workspace',
                        'thumb' => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'ws-2',
                        'title' => 'Warm Laptop Desk',
                        'thumb' => 'https://images.unsplash.com/photo-1497032628192-86f99bcd76bc?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1497032628192-86f99bcd76bc?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'ws-3',
                        'title' => 'Clean Bright Office',
                        'thumb' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'ws-4',
                        'title' => 'Collaborative Studio',
                        'thumb' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1950&q=80',
                    ],
                ],
            ],
            'study_desk' => [
                'label' => 'Study Desk',
                'items' => [
                    [
                        'id'    => 'sd-1',
                        'title' => 'Wooden Notebook & Laptop',
                        'thumb' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'sd-2',
                        'title' => 'Focused Study Table',
                        'thumb' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'sd-3',
                        'title' => 'Morning Coffee & Notes',
                        'thumb' => 'https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'sd-4',
                        'title' => 'Minimal Desk Setup',
                        'thumb' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1950&q=80',
                    ],
                ],
            ],
            'library' => [
                'label' => 'Library',
                'items' => [
                    [
                        'id'    => 'lib-1',
                        'title' => 'Classic Academic Library',
                        'thumb' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'lib-2',
                        'title' => 'Quiet Reading Hall',
                        'thumb' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'lib-3',
                        'title' => 'Bookshelf Corridor',
                        'thumb' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'lib-4',
                        'title' => 'Modern Study Commons',
                        'thumb' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1950&q=80',
                    ],
                ],
            ],
            'university_campus' => [
                'label' => 'University Campus',
                'items' => [
                    [
                        'id'    => 'uc-1',
                        'title' => 'Campus Architecture',
                        'thumb' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'uc-2',
                        'title' => 'University Campus Courtyard',
                        'thumb' => 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'uc-3',
                        'title' => 'Campus Courtyard',
                        'thumb' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'uc-4',
                        'title' => 'Academic Building',
                        'thumb' => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=1950&q=80',
                    ],
                ],
            ],
            'dark_minimal_desk' => [
                'label' => 'Dark Minimal Desk',
                'items' => [
                    [
                        'id'    => 'dm-1',
                        'title' => 'Dark Tech Hardware',
                        'thumb' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'dm-2',
                        'title' => 'Dark Minimal Workstation',
                        'thumb' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'dm-3',
                        'title' => 'Night Coding Desk',
                        'thumb' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'dm-4',
                        'title' => 'Abstract Dark Geometry',
                        'thumb' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1950&q=80',
                    ],
                ],
            ],
            'education' => [
                'label' => 'Education',
                'items' => [
                    [
                        'id'    => 'ed-1',
                        'title' => 'Classroom & Learning',
                        'thumb' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'ed-2',
                        'title' => 'Digital Assessment',
                        'thumb' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'ed-3',
                        'title' => 'Academic Seminar',
                        'thumb' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1950&q=80',
                    ],
                    [
                        'id'    => 'ed-4',
                        'title' => 'Science & Research',
                        'thumb' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=480&q=75',
                        'full'  => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=1950&q=80',
                    ],
                ],
            ],
        ];
    }

    /**
     * Halaman Pengaturan Background Portal SBA
     */
    public function backgroundPortal()
    {
        $bgConfig = SbaSetting::getPortalBackground();
        $catalog  = $this->getCuratedUnsplashCatalog();
        $hasUnsplashKey = !empty(env('UNSPLASH_ACCESS_KEY'));

        return view('sba.settings.bgportal', compact('bgConfig', 'catalog', 'hasUnsplashKey'));
    }

    /**
     * Endpoint AJAX untuk Unsplash Live (Official API jika key tersedia, atau fallback pool kurasi acak)
     */
    public function fetchUnsplashLive(Request $request)
    {
        $query = trim((string) $request->query('query', 'workspace'));
        $allowedQueries = [
            'workspace'         => 'modern workspace desk',
            'study_desk'        => 'study desk laptop notebook',
            'library'           => 'university library books',
            'university_campus' => 'university campus building',
            'dark_minimal_desk' => 'dark minimal desk setup',
            'education'         => 'education classroom learning',
        ];

        $searchQuery = $allowedQueries[$query] ?? ($query !== '' ? $query : 'workspace');
        $accessKey   = trim((string) config('services.unsplash.key', env('UNSPLASH_ACCESS_KEY', '')));
        $apiError    = null;

        if ($accessKey !== '') {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Accept-Version' => 'v1',
                        'Authorization'  => 'Client-ID ' . $accessKey,
                        'Cache-Control'  => 'no-cache',
                    ])
                    ->get('https://api.unsplash.com/photos/random', [
                        'query'       => $searchQuery,
                        'orientation' => 'landscape',
                        'count'       => 6,
                        'sig'         => uniqid('', true), // Mencegah cache URL
                    ]);

                if ($response->successful()) {
                    $photos = collect($response->json())->map(function ($item) {
                        $rawUrl = $item['urls']['raw'] ?? ($item['urls']['regular'] ?? '');
                        $fullUrl = str_contains($rawUrl, '?')
                            ? $rawUrl . '&auto=format&fit=crop&w=1950&q=80'
                            : $rawUrl . '?auto=format&fit=crop&w=1950&q=80';

                        return [
                            'id'     => $item['id'] ?? uniqid('unsp_'),
                            'title'  => $item['alt_description'] ?? ($item['description'] ?? 'Unsplash Photo'),
                            'author' => $item['user']['name'] ?? 'Unsplash',
                            'thumb'  => $item['urls']['small'] ?? $fullUrl,
                            'full'   => $fullUrl,
                        ];
                    })->values()->all();

                    return response()->json([
                        'status' => 'success',
                        'mode'   => 'official_api',
                        'photos' => $photos,
                    ]);
                }

                $apiError = 'HTTP ' . $response->status() . ': ' . ($response->json('errors.0') ?? 'Gagal mengakses API Unsplash');
            } catch (\Throwable $e) {
                $apiError = $e->getMessage();
            }
        }

        // Fallback jika API Key kosong / gagal / limit habis: filter sesuai kategori tema
        $catalog = $this->getCuratedUnsplashCatalog();
        $targetCategories = isset($catalog[$query]) ? [$query => $catalog[$query]] : $catalog;

        $pool = [];
        foreach ($targetCategories as $catData) {
            foreach ($catData['items'] as $item) {
                $pool[] = [
                    'id'     => $item['id'],
                    'title'  => $item['title'] . ' (' . $catData['label'] . ')',
                    'author' => 'Unsplash Curated',
                    'thumb'  => $item['thumb'],
                    'full'   => $item['full'],
                ];
            }
        }

        shuffle($pool);

        return response()->json([
            'status'    => 'success',
            'mode'      => 'curated_pool',
            'api_error' => $apiError,
            'photos'    => array_slice($pool, 0, 6),
        ]);
    }

    /**
     * Simpan Pengaturan Background Portal SBA
     */
    public function updateBackgroundPortal(Request $request)
    {
        $request->validate([
            'source_type'     => 'required|in:unsplash_preset,static_color,unsplash_live,image_url,upload',
            'preset_category' => 'nullable|string|max:50',
            'selected_url'    => 'nullable|url|max:1000',
            'custom_url'      => 'nullable|url|max:1000',
            'upload_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'color_mode'      => 'nullable|in:solid,gradient',
            'solid_color'     => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'gradient_start'  => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'gradient_end'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'gradient_angle'  => 'nullable|in:90deg,135deg,180deg,45deg',
            'overlay_opacity' => 'nullable|numeric|min:0|max:0.9',
            'failover_color'  => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ], [
            'selected_url.url'   => 'Silakan pilih salah satu thumbnail gambar terlebih dahulu.',
            'custom_url.url'     => 'Format URL gambar eksternal tidak valid (harus diawali http:// atau https://).',
            'upload_image.image' => 'File yang diunggah harus berupa gambar.',
            'upload_image.mimes' => 'Format gambar yang diizinkan hanya JPG, JPEG, PNG, atau WEBP.',
            'upload_image.max'   => 'Ukuran file gambar maksimal 2MB (2048 KB) agar portal tetap ringan.',
        ]);

        $current = SbaSetting::getValue('portal_background', SbaSetting::defaultPortalBackground());
        $sourceType = $request->input('source_type');

        $newData = [
            'source_type'     => $sourceType,
            'preset_category' => $request->input('preset_category', $current['preset_category'] ?? 'workspace'),
            'image_url'       => $current['image_url'] ?? null,
            'upload_path'     => null, // Default null agar file upload lama otomatis terhapus jika pindah mode
            'color_mode'      => $request->input('color_mode', $current['color_mode'] ?? 'solid'),
            'solid_color'     => $request->input('solid_color', $current['solid_color'] ?? '#eef2f7'),
            'gradient_start'  => $request->input('gradient_start', $current['gradient_start'] ?? '#6f42c1'),
            'gradient_end'    => $request->input('gradient_end', $current['gradient_end'] ?? '#3b1d7a'),
            'gradient_angle'  => $request->input('gradient_angle', $current['gradient_angle'] ?? '135deg'),
            'overlay_opacity' => (float) $request->input('overlay_opacity', $current['overlay_opacity'] ?? 0.60),
            'failover_color'  => $request->input('failover_color', '#eef2f7'),
        ];

        if (in_array($sourceType, ['unsplash_preset', 'unsplash_live'], true)) {
            $selectedUrl = trim((string) $request->input('selected_url'));
            if ($selectedUrl === '') {
                return back()->withInput()->withErrors(['selected_url' => 'Silakan klik salah satu thumbnail gambar untuk dipilih.']);
            }
            $newData['image_url'] = $selectedUrl;
        } elseif ($sourceType === 'image_url') {
            $customUrl = trim((string) $request->input('custom_url'));
            if ($customUrl === '') {
                return back()->withInput()->withErrors(['custom_url' => 'Masukkan URL gambar yang valid.']);
            }
            $newData['image_url'] = $customUrl;
        } elseif ($sourceType === 'upload') {
            if ($request->hasFile('upload_image')) {
                $path = $request->file('upload_image')->store('sba/portal-bg', 'public');
                $newData['upload_path'] = $path;
            } elseif (!empty($current['upload_path'])) {
                // Pertahankan file upload yang sudah ada jika admin hanya mengubah opacity/failover
                $newData['upload_path'] = $current['upload_path'];
            } else {
                return back()->withInput()->withErrors(['upload_image' => 'Silakan pilih file gambar dari komputer Anda untuk diunggah.']);
            }
        }

        SbaSetting::setValue('portal_background', $newData);

        return redirect()
            ->route('sba.settings.background_portal')
            ->with('success', 'Pengaturan Background Portal SBA berhasil disimpan dan diterapkan!');
    }
}