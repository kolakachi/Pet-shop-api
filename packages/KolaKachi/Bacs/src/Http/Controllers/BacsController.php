<?php

namespace KolaKachi\Bacs\Http\Controllers;

use App\Http\Controllers\Controller;

class BacsController extends Controller
{
    public function getBacsResponse()
    {
        $response = [
            'data' => [
                'vol' => 'VOL1Mk2OPn0                              BACSNO                                1',
                'hdr1' => 'HDR1ABACSNOS   BACSNOMk2OPn00010001100010 22087 2308900000003LUNL7m9p1lfZ       ',
                'hdr2' => 'HDR2F0200000106                                   00                            ',
                'uhl' => 'UHL1 22087999999    000000004 MULTI  721       AUD5020                          ',
                'standard' => [
                    '1234561234567800N12345612345678/RO100000010000Test              123&abc           TestTestTestTestTe 22087',
                ],
                'eof1' => 'EOF1ABACSNOS   BACSNOMk2OPn00010001100010 22087 230890UEg9lb3LUNL7m9p1lfZ       ',
                'eof2' => 'EOF2F0200000106                                   00                            ',
                'utl' => 'UTL10000000000000000000000000000000000000000        0000001                     ',
            ],
        ];

        return response()->json($response);
    }
}
