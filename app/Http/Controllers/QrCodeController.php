<?php

namespace App\Http\Controllers;

use App\KeyPool;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
      * display qrcode.
      *
      * @param string $slug
      * @return Illuminate\Support\Facades\View
      */
    public function showImage(string $slug)
    {
        $timestamp = time();
        $key = KeyPool::where('slug', $slug)->first();
        $hashKey = md5($key->key . '+' . $timestamp);
        $qrCode = new QrCode();
        $qrCode->setText($hashKey . '+' . $timestamp);
        $qrCode->setSize(250);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(10);

        $data = [
            'slug' => $slug,
            'image' => $qrCode->writeDataUri(),
            'timestamp' => time(),
        ];
        return view('qrcode', $data);
    }
}
