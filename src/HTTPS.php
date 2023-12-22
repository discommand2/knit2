<?php

namespace RPurinton\Knit2;

class HTTPS
{
    static function post(string $url, array $headers = [], string $body = ''): string
    {
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36';
        $response = file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body
            ]
        ]));
        return $response;
    }
}
