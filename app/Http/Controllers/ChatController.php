<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Chats;
use App\Files;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ChatController extends Controller
{
    public function add_table()
    {
        $data = $this->validate(\request(), [
            'sender' => 'required|numeric',
            'receiver' => 'required|numeric',
        ]);
        $K = $data['name'] = 'chat_' . \request('sender') . '_' . \request('receiver');
        $j = 'chat_' . \request('receiver') . '_' . \request('sender');
        $s = Schema::create($K, function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->longText('message');
            $table->enum('readed', ['yes', 'no'])->default('no');
            $table->enum('sender', ['yes', 'no'])->default('no');
            $table->enum('type', ['text', 'image'])->default('text');
            $table->timestamps();
        });
        Schema::create($j, function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->longText('message');
            $table->enum('readed', ['yes', 'no'])->default('no');
            $table->enum('sender', ['yes', 'no'])->default('no');
            $table->enum('type', ['text', 'image'])->default('text');

            $table->timestamps();
        });
        Chats::create($data);
        $swp = $data['sender'];
        $data['sender'] = $data['receiver'];
        $data['receiver'] = $swp;
        $data['name'] = 'chat_' . \request('receiver') . '_' . \request('sender');
        Chats::create($data);
        return response(['status' => true, 'schema' => $s], 200);

    }

    public function check_chats()
    {
        $st = 'chat_' . \request('sender') . '_' . \request('receiver');
        $table = \App\Chats::all();
        foreach ($table as $t) {
            if ($t->name == $st) {
                return response(['status' => true, 'is' => 'found', 'd' => $st], 200);
            }
        }
        return response(['status' => true, 'is' => 'not found', 'd' => $st], 200);

    }


    public function save_message()
    {
        $this->pdo = new \PDO("mysql:host=localhost;dbname=demochat", 'root', '555');
        if (\request('type') == 'text') {
            $query = 'insert into chat_' . \request('sender') . '_' . \request('receiver') . ' (message,readed,created_at,sender) values ("' . \request('message') .
                '","yes","' . \Carbon\Carbon::parse(now()->toDateTimeString())->format('Y-m-d H:i:s') . '","yes");';

            $query2 = 'insert into chat_' . \request('receiver') . '_' . \request('sender') . ' (message,readed,created_at,sender) values ("' . \request('message') . '","no","'
                . \Carbon\Carbon::parse(now()->toDateTimeString())->format('Y-m-d H:i:s') . '","no");';

            $t = $this->pdo->exec($query);
            $t = $this->pdo->exec($query2);
            $tb['table'] = 'chat_' . \request('sender') . '_' . \request('receiver');
            return response(['status' => true, 'query' => \Carbon\Carbon::parse(now()->toDateTimeString())->format('Y-m-d H:i:s')], 200);
        }elseif (\request('type')=='file')
        {
            if (\request()->hasFile('message'))
            {
                $data=$this->validate(\request(),[

                    'message'=>'required|image|mimes:jpg,jpeg,png,gif',
                ]);
                $msg=$this->upload([

                    'file'=>'message',
                    'path'=>'users/'.'chat_' . \request('sender') . '_' . \request('receiver'),
                    'upload_type'=>'single',
                    'delete_file'=>''
                ]);

                $msg2=$this->upload([

                    'file'=>'message',
                    'path'=>'users/'.'chat_' . \request('receiver') . '_' . \request('sender'),
                    'upload_type'=>'single',
                    'delete_file'=>''
                ]);

                $query = 'insert into chat_' . \request('sender') . '_' . \request('receiver') . ' (message,readed,created_at,sender,type) values ("' . $msg .
                    '","yes","' . \Carbon\Carbon::parse(now()->toDateTimeString())->format('Y-m-d H:i:s') . '","yes","image");';
                $query2 = 'insert into chat_' . \request('receiver') . '_' . \request('sender') . ' (message,readed,created_at,sender,type) values ("' .$msg2 . '","no","'
                    . \Carbon\Carbon::parse(now()->toDateTimeString())->format('Y-m-d H:i:s') . '","no","image");';
                $t = $this->pdo->exec($query);
                $t = $this->pdo->exec($query2);
                return response(['status' => true, 'query' => $t], 200);

            }
            else{
                return 'no file';
            }

        }
        else
        {
            return response(['status' => true, 'query' => 'error'], 200);
        }
    }
    public function get_message()
    {
        $tables = Chats::all();
        foreach ($tables as $table) {
            if ($table->name == 'chat_' . \request('me') . '_' . \request('user_id')) {
                $this->pdo = new \PDO("mysql:host=localhost;dbname=demochat", 'root', '555');
                $query = 'select * from ' . 'chat_' . \request('me') . '_' . \request('user_id');
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                // $stmt->fetchAll();
                return response(['status' => true, 'iss' => $stmt->fetchAll()], 200);
            }
        }

        return response(['query' => 'not found'], 200);
    }

    public function readed()
    {
        $tables = Chats::all();
        $this->pdo = new \PDO("mysql:host=localhost;dbname=demochat", 'root', '555');
        foreach ($tables as $table) {
            if ($table->name == 'chat_' . \request('sender') . '_' . \request('receiver')) {

                $query = 'update ' . 'chat_' . \request('sender') . '_' . \request('receiver').' set readed="yes" where readed="no"';
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                // $stmt->fetchAll();
                return response(['status' => true], 200);
            }
        }

        return response(['query' => 'not found'], 200);

    }

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
    public  function return_image()
    {
        $image=view('image',['image'=>\request('ur')])->render();

        return \response(['io'=>$image,'index'=>\request('index')]);
    }
}
