<?php

namespace OrckidLab\FileManager\Database\Seeds;

use Illuminate\Database\Seeder;
use OrckidLab\FileManager\Core\Model\Upload;


class UploadsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Upload::create([
            'name' => 'uploads',
            'type' => 'directory',
            'size' => 0,
            'token' => '6874daaa8b37f85ea3e5c68050b78d2c',
            'parent_id' => null
        ]);
    }
}
