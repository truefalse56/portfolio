<?php

declare(strict_types=1);

namespace App\Support;

use Psr\Http\Message\ResponseInterface;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require base_path('templates/' . $template . '.php');
        return (string) ob_get_clean();
    }

    public static function page(string $template, array $data = [], int $status = 200): ResponseInterface
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require base_path('templates/header.php');
        require base_path('templates/pages/' . $template . '.php');
        require base_path('templates/footer.php');
        $html = (string) ob_get_clean();

        return self::html($html, $status);
    }

    public static function admin(string $template, array $data = [], int $status = 200): ResponseInterface
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require base_path('templates/admin/layout_top.php');
        require base_path('templates/admin/' . $template . '.php');
        require base_path('templates/admin/layout_bottom.php');
        $html = (string) ob_get_clean();

        return self::html($html, $status);
    }

    public static function html(string $html, int $status = 200, string $contentType = 'text/html; charset=UTF-8'): ResponseInterface
    {
        $response = new \Slim\Psr7\Response($status);
        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', $contentType);
    }

    public static function redirect(string $url, int $status = 302): ResponseInterface
    {
        return (new \Slim\Psr7\Response($status))->withHeader('Location', $url);
    }
}
