<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppTwigExtension extends AbstractExtension
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {

        return [
            new TwigFunction('pluralize', [$this, 'doSomething']),
            new TwigFunction('border_error', [$this, 'border_error']),
        ];
    }

    public function doSomething(int $len,string $singular, ?string $plural = null)
    {
        $plural = $plural ?? $singular . 's';

        return ($len > 1)? $plural."($len)": $singular."($len)";
    }

    public function border_error(bool $error)
    {
        if ($error) return 'border: solid 2px #ff3333';
        else return '';
    }
}
