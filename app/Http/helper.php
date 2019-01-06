<?php

if(!function_exists('up')){
    function up()
    {
        return new \App\Http\Controllers\Upload;
    }
}

if(!function_exists('validate_images')){
    function validate_images($ext=null)
    {
        if($ext===null)
        {
            return 'image|mimes:jpg,jpeg,png,gif';
        }
        else{
            return 'image|mimes'.$ext;
        }
    }
}