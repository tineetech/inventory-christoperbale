<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        return view('pages.backup.index');
    }

    public function backup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host     = env('DB_HOST');

        $filename = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        $path = storage_path("app/" . $filename);

        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$path}";

        system($command);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}