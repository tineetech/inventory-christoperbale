<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class PermissionRouteTest extends TestCase
{
    public function test_semua_permission_route_ada_di_database()
    {

        // ambil semua permission dari DB
        $dbPermissions = DB::table('hak_akses')
            ->pluck('nama_permission')
            ->toArray();

        $missingPermissions = [];

        foreach (Route::getRoutes() as $route) {

            $middlewares = $route->gatherMiddleware();

            foreach ($middlewares as $middleware) {

                if (str_contains($middleware, 'permission:')) {

                    $permission = str_replace('permission:', '', $middleware);

                    $parts = explode(',', $permission);

                    if (count($parts) == 2) {

                        $permissionName = $parts[0] . '_' . $parts[1];

                        if (!in_array($permissionName, $dbPermissions)) {

                            $missingPermissions[] = [
                                'route' => $route->uri(),
                                'permission' => $permissionName
                            ];
                        }
                    }
                }
            }
        }

        $this->assertEmpty(
            $missingPermissions,
            "Permission berikut tidak ada di tabel hak_akses:\n" .
            json_encode($missingPermissions, JSON_PRETTY_PRINT)
        );
    }
}