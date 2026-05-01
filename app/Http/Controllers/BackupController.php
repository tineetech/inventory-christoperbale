<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        return view('pages.backup.index');
    }

    // public function backup()
    // {
    //     $database = env('DB_DATABASE');
    //     $username = env('DB_USERNAME');
    //     $password = env('DB_PASSWORD');
    //     $host     = env('DB_HOST');

    //     $filename = "backup_" . date('Y-m-d_H-i-s') . ".sql";
    //     $path = storage_path("app/" . $filename);

    //     $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$path}";

    //     system($command);

    //     return response()->download($path)->deleteFileAfterSend(true);
    // }
    public function backup()
    {
        $tables = \DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');

        $sql = "";

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];

            // structure
            $create = \DB::select("SHOW CREATE TABLE $tableName")[0];
            $sql .= "\n\n" . $create->{'Create Table'} . ";\n\n";

            // data
            $rows = \DB::table($tableName)->get();

            foreach ($rows as $row) {
                $values = array_map(fn($v) => addslashes($v), (array)$row);
                $values = "'" . implode("','", $values) . "'";
                $sql .= "INSERT INTO $tableName VALUES ($values);\n";
            }
        }

        $filename = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        $path = storage_path("app/" . $filename);

        file_put_contents($path, $sql);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}