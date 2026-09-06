<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\User;
use App\Services\Home\HomePageDataService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HomeV2PreviewTest extends TestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/home-v2-preview')->assertRedirect('/login');
    }

    public function testNonAdminCannotRenderPreview(): void
    {
        $user = new User(['user_type' => 'user']);
        $request = Request::create('/home-v2-preview');
        $request->setUserResolver(static fn(): User => $user);
        $service = $this->createMock(HomePageDataService::class);
        $service->expects($this->never())->method('get');

        try {
            (new HomeController())->previewV2($request, $service);
            $this->fail('Non-admin preview must be forbidden.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function testFullAdminCanPreviewV2WhilePublicFlagIsDisabled(): void
    {
        config(['homepage.use_v2' => false]);

        $admin = new User(['user_type' => 'admin']);
        $request = Request::create('/home-v2-preview');
        $request->setUserResolver(static fn(): User => $admin);
        $service = $this->createMock(HomePageDataService::class);
        $service->expects($this->once())
            ->method('get')
            ->with($admin)
            ->willReturn(['title' => 'Home v2 preview']);

        $view = (new HomeController())->previewV2($request, $service);

        $this->assertSame('home-v2', $view->name());
        $this->assertTrue($view->getData()['homeV2Preview']);
        $this->assertFalse(config('homepage.use_v2'));
    }
}
