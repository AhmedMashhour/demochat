<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Files;
class Upload extends Controller
{
    public static function upload($data=[])
    {
        if(\request()->hasFile($data['file'])&&$data['upload_type']=='single')
        {

            return \request()->file($data['file'])->store($data['path']);
        }else if(\request()->hasFile($data['file'])&&$data['upload_type']=='files')
        {
            $file= \request()->file($data['file']);
            $name=$file->getClientOriginalName();
            $size=$file->getSize();
            $mim=$file->getMimeType();
            $hash=$file->hashName();
            $file->store($data['path']);
            $add=Files::create([
                'name'=>$name,
                'size'=>$size,
                'file'=>$hash,
                'path'=>$data['path'],
                'full_file'=> $data['path'].'/'.$hash,
                'mime_type'=>$mim,
                'file_type'=>$data['file_type'],
            ]);
            return $add->id;
        }
    }

}
