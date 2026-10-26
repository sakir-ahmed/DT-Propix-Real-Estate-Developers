<?php

declare(strict_types=1);

use App\Models\Lead;
use App\Models\Page;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\Unit;
use Database\Seeders\SettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(SettingSeeder::class);
    $this->baseUrl = rtrim(config('app.url'), '/');
});

it('redirects the bare domain to the home page in a supported language', function () {
    $this->get('/')->assertRedirect('/en');
    $this->withHeaders(['Accept-Language' => 'bn'])->get('/')->assertRedirect('/bn');
});

it('renders every public page in both languages', function (string $locale) {
    Page::factory()->published()->create(['slug' => 'privacy']);
    Page::factory()->published()->create(['slug' => 'terms']);

    $pages = [
        '' => 'Web/Home',
        '/about' => 'Web/About',
        '/projects' => 'Web/Projects/Index',
        '/services' => 'Web/Services/Index',
        '/news' => 'Web/News/Index',
        '/careers' => 'Web/Careers/Index',
        '/team' => 'Web/Team',
        '/testimonials' => 'Web/Testimonials',
        '/faq' => 'Web/Faq',
        '/contact' => 'Web/Contact',
        '/privacy' => 'Web/Page',
        '/terms' => 'Web/Page',
    ];

    foreach ($pages as $path => $component) {
        $this->get("/{$locale}{$path}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component($component)->where('locale', $locale)->has('seo.canonical'));
    }
})->with(['en', 'bn']);

it('renders SEO head tags with canonical, hreflang alternates and JSON-LD', function () {
    $response = $this->get('/en');

    $response->assertOk()
        ->assertSee('<link rel="canonical" href="'.$this->baseUrl.'/en">', false)
        ->assertSee('hreflang="en" href="'.$this->baseUrl.'/en"', false)
        ->assertSee('hreflang="bn" href="'.$this->baseUrl.'/bn"', false)
        ->assertSee('hreflang="x-default"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('<html lang="en"', false);

    $this->get('/bn')->assertSee('<html lang="bn"', false);
});

it('shows only published projects on the website', function () {
    $published = Project::factory()->published()->create(['status' => 'ongoing']);
    $hidden = Project::factory()->create(['is_published' => false]);

    $this->get('/en/projects')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Web/Projects/Index')
            ->where('projects.total', 1)
            ->where('projects.data.0.slug', $published->slug));

    $this->get('/en/projects/'.$published->slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Web/Projects/Show')
            ->where('project.name', $published->name)
            ->has('seo.json_ld', 2));

    $this->get('/en/projects/'.$hidden->slug)->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSee($hidden->slug);
});

it('hides booked and sold units unless the project shows them, and hides prices on request', function () {
    $project = Project::factory()->published()->create(['show_prices' => false, 'show_sold_units' => false]);
    Unit::factory()->create(['project_id' => $project->id, 'number' => 'A-1', 'status' => 'available']);
    Unit::factory()->create(['project_id' => $project->id, 'number' => 'A-2', 'status' => 'sold']);
    Unit::factory()->create(['project_id' => $project->id, 'number' => 'A-3', 'status' => 'on_hold']);

    $this->get('/en/projects/'.$project->slug)
        ->assertInertia(fn (Assert $page) => $page
            ->has('project.units', 1)
            ->where('project.units.0.number', 'A-1')
            ->where('project.units.0.price', null)
            ->where('project.starting_price', null));

    $project->update(['show_sold_units' => true, 'show_prices' => true]);

    $this->get('/en/projects/'.$project->slug)
        ->assertInertia(fn (Assert $page) => $page
            ->has('project.units', 2)
            ->where('project.units.1.status', 'sold')
            ->where('project.units.1.price', null)
            ->whereNot('project.units.0.price', null));
});

it('removes a unit from the public page as soon as it is booked', function () {
    $project = Project::factory()->published()->create();
    $unit = Unit::factory()->create(['project_id' => $project->id, 'status' => 'available']);

    $this->get('/en/projects/'.$project->slug)->assertInertia(fn (Assert $page) => $page->has('project.units', 1));

    $unit->update(['status' => 'booked']);

    $this->get('/en/projects/'.$project->slug)->assertInertia(fn (Assert $page) => $page->has('project.units', 0));
});

it('captures an enquiry once per submission token and merges a repeat mobile', function () {
    $project = Project::factory()->published()->create();
    $payload = [
        'kind' => 'site_visit',
        'name' => 'Rafiq Uddin',
        'mobile' => '01711 000001',
        'email' => 'rafiq@example.com',
        'project_id' => $project->id,
        'message' => 'Weekend visit please',
        'preferred_date' => now()->addDays(3)->toDateString(),
        'consent' => true,
        'submission_token' => 'token-1',
        'utm_source' => 'facebook',
        'utm_campaign' => 'eid-offer',
    ];

    $this->post('/en/enquiries', $payload)->assertRedirect()->assertSessionHas('success');
    $this->post('/en/enquiries', $payload)->assertRedirect()->assertSessionHas('success');

    expect(Lead::query()->count())->toBe(1);

    $lead = Lead::query()->firstOrFail();
    expect($lead->mobile)->toBe('8801711000001')
        ->and($lead->source)->toBe('website')
        ->and($lead->campaign)->toBe('eid-offer')
        ->and($lead->project_id)->toBe($project->id)
        ->and($lead->activities()->first()?->note)->toContain('Site visit request');

    $this->post('/en/enquiries', [...$payload, 'submission_token' => 'token-2', 'kind' => 'callback'])->assertRedirect();

    expect(Lead::query()->count())->toBe(1)
        ->and($lead->activities()->count())->toBe(2);
});

it('drops honeypot submissions silently and validates required fields', function () {
    $this->post('/en/enquiries', [
        'kind' => 'enquiry',
        'name' => 'Bot',
        'mobile' => '01711000009',
        'consent' => true,
        'submission_token' => 'bot-token',
        'company_website' => 'https://spam.example',
    ])->assertRedirect()->assertSessionHas('success');

    expect(Lead::query()->count())->toBe(0);

    $this->post('/en/enquiries', ['kind' => 'enquiry', 'name' => '', 'mobile' => '12345', 'submission_token' => 'x'])
        ->assertSessionHasErrors(['name', 'mobile', 'consent']);

    expect(Lead::query()->count())->toBe(0);
});

it('serves a sitemap with hreflang alternates and a robots file pointing to it', function () {
    $project = Project::factory()->published()->create();

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<loc>'.$this->baseUrl.'/en/projects/'.$project->slug.'</loc>', false)
        ->assertSee('<loc>'.$this->baseUrl.'/bn/projects/'.$project->slug.'</loc>', false)
        ->assertSee('hreflang="x-default"', false);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.$this->baseUrl.'/sitemap.xml');
});

it('follows editor-managed redirects', function () {
    Redirect::factory()->create(['from_path' => '/old-flats', 'to_path' => '/en/projects', 'status_code' => 301]);

    $this->get('/old-flats')->assertRedirect('/en/projects')->assertStatus(301);

    expect(Redirect::query()->firstOrFail()->hits)->toBe(1);
});
