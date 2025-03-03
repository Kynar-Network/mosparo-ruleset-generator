<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $env;

    public function __construct(string $env)
    {
        $this->env = $env;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_env', [$this, 'getEnv']),
        ];
    }

    public function getEnv(string $name): string
    {
        return $_ENV[$name] ?? '';
    }
}
